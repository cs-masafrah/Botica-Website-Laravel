<?php

namespace Webkul\Product\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductsByAttributeEavQuery
{
    /**
     * Product repository instance
     */
    protected $productRepository;

    /**
     * Attribute repository instance
     */
    protected $attributeRepository;

    /**
     * Attribute option repository instance
     */
    protected $attributeOptionRepository;

    /**
     * Direct product table columns that can be used for sorting
     */
    protected $directColumns = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * Create a new query instance
     */
    public function __construct(
        ProductRepository $productRepository,
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * Get products filtered by multiple attribute values with EAV-compatible sorting
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context)
    {
        try {
            $input = $args['input'];
            $perPage = $input['perPage'] ?? 10;
            $page = $input['page'] ?? 1;

            // Build the query with EAV sorting
            $query = $this->buildEavQuery($input);

            // Log the SQL for debugging (optional)
            Log::debug('ProductsByAttributeEavQuery SQL', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            // Get total count
            $total = $query->count();

            // Calculate pagination
            $offset = ($page - 1) * $perPage;
            $lastPage = $total > 0 ? max(ceil($total / $perPage), 1) : 1;

            // Get paginated data
            $data = $query->skip($offset)
                ->take($perPage)
                ->get();

            return [
                'paginatorInfo' => [
                    'count' => $data->count(),
                    'currentPage' => (int) $page,
                    'lastPage' => (int) $lastPage,
                    'total' => $total,
                    'perPage' => (int) $perPage,
                    'hasMorePages' => $page < $lastPage,
                    'firstItem' => $total > 0 ? $offset + 1 : null,
                    'lastItem' => $total > 0 ? min($offset + $perPage, $total) : null,
                ],
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('ProductsByAttributeEavQuery Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Build query with multiple attribute filters
     */
    protected function buildEavQuery($input)
    {
        // Start building query - use the product repository's query builder
        $query = $this->productRepository->scopeQuery(function ($query) {
            return $query->distinct();
        })->newQuery();

        // Get filters array (if not provided, get all products)
        $filters = $input['filters'] ?? [];

        // Apply attribute filters if provided
        if (!empty($filters)) {
            foreach ($filters as $index => $filter) {
                $attributeCode = $filter['attribute'];
                $values = $filter['value']; // This is an array

                // Determine operator
                $operator = $filter['operator'] ?? (count($values) > 1 ? 'in' : 'eq');

                // Find the attribute
                $attribute = $this->findAttribute($attributeCode);

                if (!$attribute) {
                    throw new \Exception("Attribute '{$attributeCode}' not found");
                }

                // Apply filter for this attribute
                $this->applyAttributeFilter($query, $attribute, $values, $operator, $index);
            }
        }

        // Apply other filters
        $query = $this->applyOtherFilters($query, $input);

        // Apply search if provided
        if (isset($input['search']) && !empty($input['search'])) {
            $query = $this->applySearchFilter($query, $input['search']);
        }

        // Apply EAV-compatible sorting
        $sortBy = $input['sortBy'] ?? 'created_at';
        $sortOrder = $this->validateSortOrder($input['sortOrder'] ?? 'desc');

        $query = $this->applyEavSorting($query, $sortBy, $sortOrder);

        return $query;
    }

    /**
     * Find attribute by code or admin_name
     */
    protected function findAttribute($attributeCode)
    {
        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
        }

        return $attribute;
    }

    /**
     * Apply single attribute filter
     */
    protected function applyAttributeFilter($query, $attribute, $values, $operator, $index)
    {
        $alias = "attr_filter_{$index}_{$attribute->id}";

        // Join with product_attribute_values
        $query->leftJoin("product_attribute_values as {$alias}", function ($join) use ($alias, $attribute) {
            $join->on('products.id', '=', "{$alias}.product_id")
                ->where("{$alias}.attribute_id", '=', $attribute->id);
        });

        // Determine which column to filter based on attribute type
        $column = $this->getAttributeValueColumn($attribute);

        switch ($operator) {
            case 'in':
                // Handle multiple values
                $this->applyInFilter($query, $alias, $column, $attribute, $values);
                break;

            case 'like':
                $query->where("{$alias}.text_value", 'like', '%' . $values[0] . '%');
                break;

            case 'gt':
                $query->where("{$alias}.float_value", '>', (float) $values[0]);
                break;

            case 'gte':
                $query->where("{$alias}.float_value", '>=', (float) $values[0]);
                break;

            case 'lt':
                $query->where("{$alias}.float_value", '<', (float) $values[0]);
                break;

            case 'lte':
                $query->where("{$alias}.float_value", '<=', (float) $values[0]);
                break;

            case 'eq':
            default:
                $this->applyEqFilter($query, $alias, $column, $attribute, $values[0]);
                break;
        }
    }

    /**
     * Get the appropriate column for attribute value based on attribute type
     */
    protected function getAttributeValueColumn($attribute)
    {
        switch ($attribute->type) {
            case 'select':
            case 'multiselect':
            case 'boolean':
                return 'integer_value';
            case 'date':
            case 'datetime':
                return 'datetime_value';
            case 'price':
            case 'float':
                return 'float_value';
            case 'text':
            case 'textarea':
            default:
                return 'text_value';
        }
    }

    /**
     * Apply IN filter for multiple values
     */
    protected function applyInFilter($query, $alias, $column, $attribute, $values)
    {
        if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
            // Get option IDs for the values
            $optionIds = $this->getOptionIdsForValues($attribute, $values);

            if (!empty($optionIds)) {
                $query->whereIn("{$alias}.integer_value", $optionIds);
            } else {
                // Fallback to text values
                $query->whereIn("{$alias}.text_value", $values);
            }
        } else {
            // For non-select attributes, filter by the appropriate column
            $query->whereIn("{$alias}.{$column}", $values);
        }
    }

    /**
     * Apply EQUAL filter for single value
     */
    protected function applyEqFilter($query, $alias, $column, $attribute, $value)
    {
        if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
            // Get option ID for the value
            $optionId = $this->getOptionIdForValue($attribute, $value);

            if ($optionId) {
                $query->where("{$alias}.integer_value", $optionId);
            } else {
                // Try to find by admin_name or use the value as-is
                $option = $this->attributeOptionRepository->findWhere([
                    'attribute_id' => $attribute->id,
                    'admin_name' => $value
                ])->first();

                if ($option) {
                    $query->where("{$alias}.integer_value", $option->id);
                } else {
                    $query->where("{$alias}.text_value", $value);
                }
            }
        } elseif ($attribute->type === 'price' || $attribute->type === 'float') {
            $query->where("{$alias}.float_value", (float) $value);
        } elseif ($attribute->type === 'boolean') {
            $query->where("{$alias}.integer_value", (int) $value);
        } elseif ($attribute->type === 'date' || $attribute->type === 'datetime') {
            $query->where("{$alias}.datetime_value", $value);
        } else {
            $query->where("{$alias}.text_value", $value);
        }
    }

    /**
     * Get option ID for a single value
     */
    protected function getOptionIdForValue($attribute, $value)
    {
        // Try to find by ID first
        if (is_numeric($value)) {
            $option = $this->attributeOptionRepository->findWhere([
                'attribute_id' => $attribute->id,
                'id' => $value
            ])->first();

            if ($option) {
                return $option->id;
            }
        }

        // Try to find by admin_name
        $option = $this->attributeOptionRepository->findWhere([
            'attribute_id' => $attribute->id,
            'admin_name' => $value
        ])->first();

        return $option ? $option->id : null;
    }

    /**
     * Get option IDs for multiple values
     */
    protected function getOptionIdsForValues($attribute, $values)
    {
        $optionIds = [];

        foreach ($values as $value) {
            $optionId = $this->getOptionIdForValue($attribute, $value);
            if ($optionId) {
                $optionIds[] = $optionId;
            }
        }

        return $optionIds;
    }

    /**
     * Apply other filters (categories, price, stock)
     */
    protected function applyOtherFilters($query, $input)
    {
        // Filter by categories
        if (isset($input['categories']) && !empty($input['categories'])) {
            $query->whereHas('categories', function ($q) use ($input) {
                $q->whereIn('categories.id', $input['categories']);
            });
        }

        // Filter by price range
        if (isset($input['min_price']) || isset($input['max_price'])) {
            $priceAttribute = $this->findAttribute('price');

            if ($priceAttribute) {
                $query->whereHas('attribute_values', function ($q) use ($priceAttribute, $input) {
                    $q->where('attribute_id', $priceAttribute->id);

                    if (isset($input['min_price'])) {
                        $q->where('float_value', '>=', (float) $input['min_price']);
                    }

                    if (isset($input['max_price'])) {
                        $q->where('float_value', '<=', (float) $input['max_price']);
                    }
                });
            }
        }

        // Filter by stock
        if (isset($input['in_stock']) && $input['in_stock']) {
            $query->whereHas('inventories', function ($q) {
                $q->where('qty', '>', 0);
            });
        }

        // Filter by status (active products only)
        $statusAttribute = $this->findAttribute('status');
        if ($statusAttribute) {
            $query->whereHas('attribute_values', function ($q) use ($statusAttribute) {
                $q->where('attribute_id', $statusAttribute->id)
                    ->where('boolean_value', 1);
            });
        }

        // Filter by visible individually
        $visibleAttribute = $this->findAttribute('visible_individually');
        if ($visibleAttribute) {
            $query->whereHas('attribute_values', function ($q) use ($visibleAttribute) {
                $q->where('attribute_id', $visibleAttribute->id)
                    ->where('boolean_value', 1);
            });
        }

        return $query;
    }

    /**
     * Apply search filter on name and description
     */
    protected function applySearchFilter($query, $searchTerm)
    {
        $nameAttribute = $this->findAttribute('name');
        $descAttribute = $this->findAttribute('description');
        $shortDescAttribute = $this->findAttribute('short_description');

        if ($nameAttribute || $descAttribute || $shortDescAttribute) {
            $query->where(function ($q) use ($searchTerm, $nameAttribute, $descAttribute, $shortDescAttribute) {
                if ($nameAttribute) {
                    $q->orWhereHas('attribute_values', function ($sq) use ($nameAttribute, $searchTerm) {
                        $sq->where('attribute_id', $nameAttribute->id)
                            ->where('text_value', 'like', '%' . $searchTerm . '%');
                    });
                }

                if ($descAttribute) {
                    $q->orWhereHas('attribute_values', function ($sq) use ($descAttribute, $searchTerm) {
                        $sq->where('attribute_id', $descAttribute->id)
                            ->where('text_value', 'like', '%' . $searchTerm . '%');
                    });
                }

                if ($shortDescAttribute) {
                    $q->orWhereHas('attribute_values', function ($sq) use ($shortDescAttribute, $searchTerm) {
                        $sq->where('attribute_id', $shortDescAttribute->id)
                            ->where('text_value', 'like', '%' . $searchTerm . '%');
                    });
                }
            });
        }

        return $query;
    }

    /**
     * Apply EAV-compatible sorting
     */
    protected function applyEavSorting($query, $sortBy, $sortOrder)
    {
        // If sorting by direct product table columns
        if (in_array($sortBy, $this->directColumns)) {
            return $query->orderBy('products.' . $sortBy, $sortOrder);
        }

        // For EAV attributes
        $attribute = $this->findAttribute($sortBy);

        if ($attribute) {
            // Join with attribute_values for sorting
            $query->leftJoin('product_attribute_values as sort_values', function ($join) use ($attribute) {
                $join->on('products.id', '=', 'sort_values.product_id')
                    ->where('sort_values.attribute_id', '=', $attribute->id);
            });

            // Determine which column to sort by based on attribute type
            switch ($attribute->type) {
                case 'select':
                case 'multiselect':
                case 'boolean':
                    $query->orderBy('sort_values.integer_value', $sortOrder);
                    break;
                case 'date':
                case 'datetime':
                    $query->orderBy('sort_values.datetime_value', $sortOrder);
                    break;
                case 'price':
                case 'float':
                    $query->orderBy('sort_values.float_value', $sortOrder);
                    break;
                case 'text':
                case 'textarea':
                case 'sku':
                default:
                    $query->orderBy('sort_values.text_value', $sortOrder);
            }

            // Select distinct products to avoid duplicates
            $query->select('products.*')->distinct();
        } else {
            // Fallback to created_at if attribute not found
            $query->orderBy('products.created_at', $sortOrder);
        }

        return $query;
    }

    /**
     * Validate sort order
     */
    protected function validateSortOrder($sortOrder)
    {
        $sortOrder = strtolower($sortOrder);

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            return 'desc';
        }

        return $sortOrder;
    }

    /**
     * Get available sort fields for debugging
     */
    public function getAvailableSortFields($rootValue, array $args, GraphQLContext $context)
    {
        // Get product table columns
        $productColumns = DB::getSchemaBuilder()->getColumnListing('products');

        // Get all attributes that can be used for sorting
        $attributes = $this->attributeRepository->all();
        $attributeCodes = $attributes->pluck('code')->toArray();

        return [
            'direct_columns' => $this->directColumns,
            'eav_attributes' => $attributeCodes,
            'all_available_fields' => array_values(array_unique(array_merge(
                $this->directColumns,
                $attributeCodes
            ))),
            'note' => 'Use these fields in sortBy parameter. Direct columns sort directly, EAV attributes sort through attribute_values table.'
        ];
    }

    /**
     * Get all values for an attribute with product counts
     */
    public function attributeValuesWithCounts($rootValue, array $args, GraphQLContext $context)
    {
        $attributeCode = $args['attribute'];
        $locale = $args['locale'] ?? app()->getLocale();

        $attribute = $this->findAttribute($attributeCode);

        if (!$attribute) {
            throw new \Exception("Attribute '{$attributeCode}' not found");
        }

        if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
            // For select attributes, get options with counts
            $options = $this->attributeOptionRepository->findByAttributeId($attribute->id);

            $result = [];
            foreach ($options as $option) {
                $count = DB::table('product_attribute_values')
                    ->where('attribute_id', $attribute->id)
                    ->where('integer_value', $option->id)
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('products')
                            ->whereColumn('products.id', 'product_attribute_values.product_id')
                            ->where('products.status', 1);
                    })
                    ->count();

                if ($count > 0) {
                    $result[] = [
                        'value' => (string) $option->id,
                        'label' => $option->admin_name ?? $option->id,
                        'product_count' => $count,
                        'option_id' => $option->id,
                        'image_url' => $option->swatch_value ? url('storage/' . $option->swatch_value) : null,
                    ];
                }
            }
        } else {
            // For other attributes, get distinct values with counts
            $values = DB::table('product_attribute_values')
                ->select('text_value', DB::raw('count(*) as count'))
                ->where('attribute_id', $attribute->id)
                ->whereNotNull('text_value')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('products')
                        ->whereColumn('products.id', 'product_attribute_values.product_id')
                        ->where('products.status', 1);
                })
                ->groupBy('text_value')
                ->orderBy('count', 'desc')
                ->limit(50)
                ->get();

            $result = [];
            foreach ($values as $value) {
                $result[] = [
                    'value' => $value->text_value,
                    'label' => $value->text_value,
                    'product_count' => $value->count,
                    'option_id' => null,
                    'image_url' => null,
                ];
            }
        }

        return [
            'attribute' => $attributeCode,
            'values' => $result,
            'total_values' => count($result),
        ];
    }
}
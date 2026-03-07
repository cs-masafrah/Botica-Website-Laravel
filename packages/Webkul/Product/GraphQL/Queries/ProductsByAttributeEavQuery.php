<?php

namespace Webkul\Product\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProductsByAttributeEavQuery
{
    protected $productRepository;
    protected $attributeRepository;

    /**
     * Direct product table columns
     */
    protected $directColumns = [
        'id',
        'created_at',
        'updated_at'
    ];

    public function __construct(
        ProductRepository $productRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Get products filtered by attribute value with EAV-compatible sorting
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context)
    {
        $input = $args['input'];
        $attributeCode = $input['attribute'];
        $value = $input['value'];
        $perPage = $input['perPage'] ?? 10;
        $page = $input['page'] ?? 1;

        // Build the query with EAV sorting
        $query = $this->buildEavQuery($attributeCode, $value, $input);

        // Get total count
        $total = $query->count();

        // Calculate pagination
        $offset = ($page - 1) * $perPage;
        $lastPage = max(ceil($total / $perPage), 1);

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
                'firstItem' => $offset + 1,
                'lastItem' => min($offset + $perPage, $total),
            ],
            'data' => $data,
        ];
    }

    /**
     * Build query with EAV-compatible sorting
     */
    protected function buildEavQuery($attributeCode, $value, $input)
    {
        // Find the attribute by code
        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            // Try by admin_name if not found by code
            $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
        }

        if (!$attribute) {
            throw new \Exception("Attribute '{$attributeCode}' not found");
        }

        // Start building query
        $query = $this->productRepository->newQuery();

        // Filter by attribute value
        $query->whereHas('attribute_values', function ($q) use ($attribute, $value) {
            $q->where('attribute_id', $attribute->id);

            if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
                $optionId = DB::table('attribute_options')
                    ->where('attribute_id', $attribute->id)
                    ->where('admin_name', $value)
                    ->value('id');

                if ($optionId) {
                    $q->where('integer_value', $optionId);
                } else {
                    $q->where('text_value', $value);
                }
            } else {
                $q->where('text_value', $value);
            }
        });

        // Apply other filters
        $query = $this->applyOtherFilters($query, $input);

        // Apply EAV-compatible sorting
        $sortBy = $input['sortBy'] ?? 'created_at';
        $sortOrder = $this->validateSortOrder($input['sortOrder'] ?? 'desc');

        $query = $this->applyEavSorting($query, $sortBy, $sortOrder);

        return $query;
    }

    /**
     * Apply EAV-compatible sorting
     */
    protected function applyEavSorting($query, $sortBy, $sortOrder)
    {
        // If sorting by direct product table columns
        if (in_array($sortBy, $this->directColumns)) {
            return $query->orderBy($sortBy, $sortOrder);
        }

        // For EAV attributes (name, price, sku, etc.)
        $attribute = $this->attributeRepository->findOneByField('code', $sortBy);

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
                    $query->orderBy('sort_values.date_value', $sortOrder);
                    break;
                case 'price':
                case 'float':
                    $query->orderBy('sort_values.float_value', $sortOrder);
                    break;
                case 'sku':
                case 'text':
                case 'textarea':
                default:
                    $query->orderBy('sort_values.text_value', $sortOrder);
            }

            // Select distinct products to avoid duplicates from join
            $query->select('products.*')->distinct();
        } else {
            // Fallback to created_at if attribute not found
            $query->orderBy('created_at', $sortOrder);
        }

        return $query;
    }

    /**
     * Apply other filters
     */
    protected function applyOtherFilters($query, $input)
    {
        // Filter by categories - FIXED: Remove slug condition
        if (isset($input['categories']) && !empty($input['categories'])) {
            $query->whereHas('categories', function ($q) use ($input) {
                if (is_array($input['categories'])) {
                    // Only filter by ID, not by slug
                    $q->whereIn('id', $input['categories']);
                }
            });
        }

        // Filter by stock
        if (isset($input['in_stock']) && $input['in_stock']) {
            if (method_exists($query->getModel(), 'inventories')) {
                $query->whereHas('inventories', function ($q) {
                    $q->where('qty', '>', 0);
                });
            }
        }

        // Filter by price range
        if (isset($input['min_price']) || isset($input['max_price'])) {
            $query = $this->applyPriceFilter($query, $input);
        }

        return $query;
    }

    /**
     * Apply price filter
     */
    protected function applyPriceFilter($query, $input)
    {
        // Get price attribute
        $priceAttribute = $this->attributeRepository->findOneByField('code', 'price');

        if ($priceAttribute) {
            $query->whereHas('attribute_values', function ($q) use ($priceAttribute, $input) {
                $q->where('attribute_id', $priceAttribute->id);

                if (isset($input['min_price'])) {
                    $q->where('float_value', '>=', $input['min_price']);
                }

                if (isset($input['max_price'])) {
                    $q->where('float_value', '<=', $input['max_price']);
                }
            });
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
}
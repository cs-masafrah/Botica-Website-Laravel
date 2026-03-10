<?php

namespace Webkul\Product\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductsByAttributeEavQuery
{
    protected $productRepository;
    protected $attributeRepository;

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

    public function __invoke($rootValue, array $args, GraphQLContext $context)
    {
        $input = $args['input'];
        $perPage = $input['perPage'] ?? 10;
        $page = $input['page'] ?? 1;

        $query = $this->buildEavQuery($input);

        $total = $query->count();

        $offset = ($page - 1) * $perPage;
        $lastPage = max(ceil($total / $perPage), 1);

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

    protected function buildEavQuery($input)
    {
        $query = $this->productRepository->newQuery();

        if (isset($input['filters']) && !empty($input['filters'])) {
            $query = $this->applyAttributeFilters($query, $input['filters']);
        }

        if (isset($input['search']) && !empty($input['search'])) {
            $query = $this->applySearchFilter($query, $input['search']);
        }

        $query = $this->applyOtherFilters($query, $input);

        $sortBy = $input['sortBy'] ?? 'created_at';
        $sortOrder = $this->validateSortOrder($input['sortOrder'] ?? 'desc');

        $query = $this->applyEavSorting($query, $sortBy, $sortOrder);

        return $query;
    }

    protected function applyAttributeFilters($query, array $filters)
    {
        foreach ($filters as $filter) {
            $attributeCode = $filter['attribute'];
            $values = $filter['value'];
            $operator = $filter['operator'] ?? 'eq';

            $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);
            if (!$attribute) {
                $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
            }
            if (!$attribute) continue;

            $query->whereHas('attribute_values', function ($q) use ($attribute, $values, $operator) {
                $q->where('attribute_id', $attribute->id);
                $column = $this->getAttributeValueColumn($attribute);

                switch ($operator) {
                    case 'eq':
                        $q->where($column, $values[0]);
                        break;
                    case 'in':
                        if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
                            $optionIds = DB::table('attribute_options')
                                ->where('attribute_id', $attribute->id)
                                ->whereIn('admin_name', $values)
                                ->pluck('id')->toArray();
                            if (!empty($optionIds)) {
                                $q->whereIn($column, $optionIds);
                            } else {
                                $q->whereRaw('1 = 0');
                            }
                        } else {
                            $q->whereIn($column, $values);
                        }
                        break;
                    case 'gte':
                        $q->where($column, '>=', $values[0]);
                        break;
                    case 'lte':
                        $q->where($column, '<=', $values[0]);
                        break;
                    case 'gt':
                        $q->where($column, '>', $values[0]);
                        break;
                    case 'lt':
                        $q->where($column, '<', $values[0]);
                        break;
                    case 'like':
                        $q->where($column, 'LIKE', '%' . $values[0] . '%');
                        break;
                }
            });
        }
        return $query;
    }

    /**
     * ✅ FIXED: Search using product_flat table (correct table name and columns)
     */
    protected function applySearchFilter($query, $searchTerm)
    {
        // Get current channel code and locale (Bagisto helpers)
        $channelCode = function_exists('core') ? core()->getCurrentChannel()->code ?? 'default' : 'default';
        $locale = app()->getLocale();

        return $query->whereExists(function ($subQuery) use ($searchTerm, $channelCode, $locale) {
            $subQuery->select(DB::raw(1))
                ->from('product_flat')
                ->whereColumn('product_flat.product_id', 'products.id')
                ->where('product_flat.channel', $channelCode)
                ->where('product_flat.locale', $locale)
                ->where(function ($q) use ($searchTerm) {
                    $q->where('product_flat.name', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('product_flat.description', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('product_flat.short_description', 'LIKE', '%' . $searchTerm . '%')
                        ->orWhere('product_flat.sku', 'LIKE', '%' . $searchTerm . '%');
                });
        });
    }

    protected function applyOtherFilters($query, $input)
    {
        if (isset($input['categories']) && !empty($input['categories'])) {
            $query->whereHas('categories', function ($q) use ($input) {
                if (is_array($input['categories'])) {
                    $q->whereIn('id', $input['categories']);
                }
            });
        }

        if (isset($input['in_stock']) && $input['in_stock']) {
            if (method_exists($query->getModel(), 'inventories')) {
                $query->whereHas('inventories', function ($q) {
                    $q->where('qty', '>', 0);
                });
            }
        }

        if (isset($input['min_price']) || isset($input['max_price'])) {
            $query = $this->applyPriceFilter($query, $input);
        }

        return $query;
    }

    protected function applyPriceFilter($query, $input)
    {
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

    protected function getAttributeValueColumn($attribute)
    {
        switch ($attribute->type) {
            case 'select':
            case 'multiselect':
            case 'boolean':
                return 'integer_value';
            case 'date':
            case 'datetime':
                return 'date_value';
            case 'price':
            case 'float':
                return 'float_value';
            default:
                return 'text_value';
        }
    }

    protected function applyEavSorting($query, $sortBy, $sortOrder)
    {
        if (in_array($sortBy, $this->directColumns)) {
            return $query->orderBy($sortBy, $sortOrder);
        }

        $attribute = $this->attributeRepository->findOneByField('code', $sortBy);
        if ($attribute) {
            $query->leftJoin('product_attribute_values as sort_values', function ($join) use ($attribute) {
                $join->on('products.id', '=', 'sort_values.product_id')
                    ->where('sort_values.attribute_id', '=', $attribute->id);
            });
            $column = $this->getAttributeValueColumn($attribute);
            $query->orderBy('sort_values.' . $column, $sortOrder);
            $query->select('products.*')->distinct();
        } else {
            $query->orderBy('created_at', $sortOrder);
        }

        return $query;
    }

    protected function validateSortOrder($sortOrder)
    {
        $sortOrder = strtolower($sortOrder);
        return in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'desc';
    }

    public function getAvailableSortFields($rootValue, array $args, GraphQLContext $context)
    {
        $attributes = $this->attributeRepository->all()->pluck('code')->toArray();
        return [
            'direct_columns' => $this->directColumns,
            'eav_attributes' => $attributes,
            'all_available_fields' => array_values(array_unique(array_merge(
                $this->directColumns,
                $attributes
            ))),
            'note' => 'Use these fields in sortBy parameter. Direct columns sort directly, EAV attributes sort through attribute_values table.'
        ];
    }
}

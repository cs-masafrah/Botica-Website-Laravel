<?php

namespace Webkul\Product\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Illuminate\Support\Facades\Storage;

class ProductsByAttributeQuery
{
    protected $productRepository;
    protected $attributeRepository;

    public function __construct(
        ProductRepository $productRepository,
        AttributeRepository $attributeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Get products filtered by attribute value
     */
    public function __invoke($rootValue, array $args, GraphQLContext $context)
    {
        $input = $args['input'];
        $attributeCode = $input['attribute'];
        $value = $input['value'];
        $perPage = $input['perPage'] ?? 10;
        $page = $input['page'] ?? 1;

        // Build the query WITHOUT price filter for now
        $query = $this->buildQuery($attributeCode, $value, $input);

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
     * Build query for filtering products by attribute value
     */
    protected function buildQuery($attributeCode, $value, $input)
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
                $optionId = \DB::table('attribute_options')
                    ->where('attribute_id', $attribute->id)
                    ->where('admin_name', $value)
                    ->value('id');

                if ($optionId) {
                    $q->where('integer_value', $optionId);
                } else {
                    $q->where('id', 0);
                }
            } else {
                $q->where('text_value', $value);
            }
        });

        // Apply other filters (excluding price filter for now)
        $query = $this->applyOtherFilters($query, $input);

        // Apply sorting
        $sortBy = $input['sortBy'] ?? 'created_at';
        $sortOrder = $input['sortOrder'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query;
    }

    /**
     * Apply other filters
     */
    protected function applyOtherFilters($query, $input)
    {
        // Filter by categories
        if (isset($input['categories']) && !empty($input['categories'])) {
            $query->whereHas('categories', function ($q) use ($input) {
                if (is_array($input['categories'])) {
                    $q->whereIn('id', $input['categories'])
                        ->orWhereIn('slug', $input['categories']);
                }
            });
        }

        // Filter by stock - simplified
        if (isset($input['in_stock']) && $input['in_stock']) {
            // Try to filter by inventory if the relationship exists
            if (method_exists($query->getModel(), 'inventories')) {
                $query->whereHas('inventories', function ($q) {
                    $q->where('qty', '>', 0);
                });
            }
            // If no inventory relationship, don't apply filter
        }

        return $query;
    }

    /**
     * Get total product count for an attribute value
     */
    public function attributeProductCount($rootValue, array $args, GraphQLContext $context)
    {
        $attributeCode = $args['attribute'];
        $value = $args['value'];

        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
        }

        if (!$attribute) {
            throw new \Exception("Attribute '{$attributeCode}' not found");
        }

        $count = $this->productRepository
            ->whereHas('attribute_values', function ($q) use ($attribute, $value) {
                $q->where('attribute_id', $attribute->id);

                if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
                    $optionId = \DB::table('attribute_options')
                        ->where('attribute_id', $attribute->id)
                        ->where('admin_name', $value)
                        ->value('id');

                    if ($optionId) {
                        $q->where('integer_value', $optionId);
                    } else {
                        $q->where('id', 0);
                    }
                } else {
                    $q->where('text_value', $value);
                }
            })
            ->count();

        return [
            'attribute' => $attributeCode,
            'value' => $value,
            'total_products' => $count
        ];
    }

    /**
     * Get all attribute values with product counts
     */
    // public function attributeValuesWithCounts($rootValue, array $args, GraphQLContext $context)
    // {
    //     $attributeCode = $args['attribute'];

    //     $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

    //     if (!$attribute) {
    //         $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
    //     }

    //     if (!$attribute) {
    //         throw new \Exception("Attribute '{$attributeCode}' not found");
    //     }

    //     $results = [];

    //     if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
    //         // For select attributes, get all options with counts
    //         $options = \DB::table('attribute_options')
    //             ->where('attribute_id', $attribute->id)
    //             ->get(['id', 'admin_name', 'image']); // Added 'image' field

    //         foreach ($options as $option) {
    //             $count = $this->productRepository
    //                 ->whereHas('attribute_values', function ($q) use ($attribute, $option) {
    //                     $q->where('attribute_id', $attribute->id)
    //                         ->where('integer_value', $option->id);
    //                 })
    //                 ->count();

    //             if ($count > 0) {
    //                 // Get image URL if exists
    //                 $imageUrl = null;
    //                 if ($option->image) {
    //                     $imageUrl = Storage::url($option->image);
    //                 }

    //                 $results[] = [
    //                     'value' => $option->admin_name,
    //                     'label' => $option->admin_name,
    //                     'product_count' => $count,
    //                     'option_id' => $option->id,
    //                     'image_url' => $imageUrl, // Added image_url field
    //                 ];
    //             }
    //         }
    //     } else {
    //         // For text attributes, get distinct values with counts
    //         $values = \DB::table('product_attribute_values')
    //             ->where('attribute_id', $attribute->id)
    //             ->whereNotNull('text_value')
    //             ->select('text_value')
    //             ->distinct()
    //             ->get();

    //         foreach ($values as $value) {
    //             $count = $this->productRepository
    //                 ->whereHas('attribute_values', function ($q) use ($attribute, $value) {
    //                     $q->where('attribute_id', $attribute->id)
    //                         ->where('text_value', $value->text_value);
    //                 })
    //                 ->count();

    //             if ($count > 0) {
    //                 $results[] = [
    //                     'value' => $value->text_value,
    //                     'label' => $value->text_value,
    //                     'product_count' => $count,
    //                     'image_url' => null, // Text attributes don't have images
    //                 ];
    //             }
    //         }
    //     }

    //     // Sort by product count descending
    //     usort($results, function ($a, $b) {
    //         return $b['product_count'] <=> $a['product_count'];
    //     });

    //     return [
    //         'attribute' => $attributeCode,
    //         'values' => $results,
    //         'total_values' => count($results)
    //     ];
    // }

    public function attributeValuesWithCounts($rootValue, array $args, GraphQLContext $context)
    {
        $attributeCode = $args['attribute'];

        // Get locale from args or use default
        $locale = $args['locale'] ?? app()->getLocale();

        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
        }

        if (!$attribute) {
            throw new \Exception("Attribute '{$attributeCode}' not found");
        }

        $results = [];

        if ($attribute->type === 'select' || $attribute->type === 'multiselect') {
            // For select attributes, get all options with counts and translations
            $options = \DB::table('attribute_options')
                ->where('attribute_id', $attribute->id)
                ->get(['id', 'admin_name', 'image']);

            // Get translations for all options in the specified locale
            $optionIds = $options->pluck('id')->toArray();
            $translations = \DB::table('attribute_option_translations')
                ->whereIn('attribute_option_id', $optionIds)
                ->where('locale', $locale)
                ->get()
                ->keyBy('attribute_option_id');

            foreach ($options as $option) {
                $count = $this->productRepository
                    ->whereHas('attribute_values', function ($q) use ($attribute, $option) {
                        $q->where('attribute_id', $attribute->id)
                            ->where('integer_value', $option->id);
                    })
                    ->count();

                if ($count > 0) {
                    // Get translated label if available, otherwise fallback to admin_name
                    $translatedLabel = isset($translations[$option->id])
                        ? $translations[$option->id]->label
                        : $option->admin_name;

                    // Get image URL if exists
                    $imageUrl = null;
                    if ($option->image) {
                        $imageUrl = Storage::url($option->image);
                    }

                    $results[] = [
                        'value' => $option->admin_name,
                        'label' => $translatedLabel, // Use translated label
                        'product_count' => $count,
                        'option_id' => $option->id,
                        'image_url' => $imageUrl,
                        'locale' => $locale, // Added locale for reference
                    ];
                }
            }
        } else {
            // For text attributes, get distinct values with counts
            $values = \DB::table('product_attribute_values')
                ->where('attribute_id', $attribute->id)
                ->whereNotNull('text_value')
                ->select('text_value')
                ->distinct()
                ->get();

            foreach ($values as $value) {
                $count = $this->productRepository
                    ->whereHas('attribute_values', function ($q) use ($attribute, $value) {
                        $q->where('attribute_id', $attribute->id)
                            ->where('text_value', $value->text_value);
                    })
                    ->count();

                if ($count > 0) {
                    $results[] = [
                        'value' => $value->text_value,
                        'label' => $value->text_value, // Text attributes don't have translations
                        'product_count' => $count,
                        'image_url' => null,
                        'locale' => $locale,
                    ];
                }
            }
        }

        // Sort by product count descending
        usort($results, function ($a, $b) {
            return $b['product_count'] <=> $a['product_count'];
        });

        return [
            'attribute' => $attributeCode,
            'values' => $results,
            'total_values' => count($results)
        ];
    }
    /**
     * Debug method to check product relationships
     */
    public function debugRelationships($rootValue, array $args, GraphQLContext $context)
    {
        $productModel = $this->productRepository;

        // Check available relationships
        $relationships = [];
        $methods = get_class_methods($productModel);

        foreach ($methods as $method) {
            if (method_exists($productModel, $method)) {
                try {
                    $reflection = new \ReflectionMethod($productModel, $method);
                    if ($reflection->isPublic() && !$reflection->isStatic()) {
                        $returnType = $reflection->getReturnType();
                        if ($returnType && (string)$returnType === 'Illuminate\Database\Eloquent\Relations\Relation') {
                            $relationships[] = $method;
                        }
                    }
                } catch (\Exception $e) {
                    // Skip
                }
            }
        }

        // Check what price-related methods exist
        $priceMethods = array_filter($methods, function ($method) {
            return stripos($method, 'price') !== false;
        });

        // Check table structure
        $columns = \DB::getSchemaBuilder()->getColumnListing('products');

        return [
            'available_relationships' => $relationships,
            'price_related_methods' => array_values($priceMethods),
            'product_table_columns' => $columns,
            'note' => 'Price filter is temporarily disabled'
        ];
    }

    /**
     * Debug query with direct SQL
     */
    public function debugQuery($rootValue, array $args, GraphQLContext $context)
    {
        $attributeCode = $args['attribute'] ?? 'brand';
        $value = $args['value'] ?? 'Dior';

        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            return ['error' => "Attribute '{$attributeCode}' not found"];
        }

        // Get option ID
        $optionId = \DB::table('attribute_options')
            ->where('attribute_id', $attribute->id)
            ->where('admin_name', $value)
            ->value('id');

        // Get product IDs directly
        $productIds = \DB::table('product_attribute_values')
            ->where('attribute_id', $attribute->id)
            ->where('integer_value', $optionId)
            ->pluck('product_id');

        // Get products
        $products = \DB::table('products')
            ->whereIn('id', $productIds)
            ->get(['id', 'name', 'sku', 'type']);

        return [
            'attribute_id' => $attribute->id,
            'option_id' => $optionId,
            'product_ids_found' => $productIds,
            'products' => $products,
            'product_count' => $products->count()
        ];
    }

    /**
     * DEBUG: Check attribute and value matching (to match GraphQL schema)
     */
    public function debug($rootValue, array $args, GraphQLContext $context)
    {
        $attributeCode = $args['attribute'] ?? 'brand';
        $value = $args['value'] ?? 'Dior';

        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            return ['error' => "Attribute '{$attributeCode}' not found"];
        }

        // Get option ID
        $optionId = \DB::table('attribute_options')
            ->where('attribute_id', $attribute->id)
            ->where('admin_name', $value)
            ->value('id');

        // Get product IDs directly
        $productIds = \DB::table('product_attribute_values')
            ->where('attribute_id', $attribute->id)
            ->where('integer_value', $optionId)
            ->pluck('product_id');

        // Get products count
        $productCount = $productIds->count();

        return [
            'attribute' => $attributeCode,
            'value' => $value,
            'attribute_id' => $attribute->id,
            'attribute_type' => $attribute->type,
            'option_id' => $optionId,
            'product_ids_found' => $productIds->toArray(),
            'product_count' => $productCount,
            'note' => 'Debug attribute and value matching'
        ];
    }
}
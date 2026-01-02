<?php

namespace Webkul\Product\GraphQL\Queries;

use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Product\Repositories\ProductRepository;

class AttributeQuery
{
    protected $attributeRepository;
    protected $attributeOptionRepository;
    protected $productRepository;

    public function __construct(
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository,
        ProductRepository $productRepository
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeOptionRepository = $attributeOptionRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Get all attributes with filtering
     */
    public function attributes($rootValue, array $args, GraphQLContext $context)
    {
        $filters = $args['input'] ?? [];

        $query = $this->attributeRepository->newQuery();

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['code'])) {
            $query->where('code', $filters['code']);
        }

        if (isset($filters['codes']) && is_array($filters['codes']) && !empty($filters['codes'])) {
            $query->whereIn('code', $filters['codes']);
        }

        if (isset($filters['is_filterable']) && $filters['is_filterable']) {
            $query->where('is_filterable', 1);
        }

        if (isset($filters['is_visible_on_front']) && $filters['is_visible_on_front']) {
            $query->where('is_visible_on_front', 1);
        }

        if (!empty($filters['product_type'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereJsonContains('product_types', $filters['product_type'])
                    ->orWhereNull('product_types')
                    ->orWhere('product_types', '[]');
            });
        }

        // Sort by position
        $query->orderBy('position');

        return $query->get();
    }

    /**
     * Get attribute by code
     */
    public function attribute($rootValue, array $args, GraphQLContext $context)
    {
        $code = $args['code'];

        $attribute = $this->attributeRepository->findOneByField('code', $code);

        if (!$attribute) {
            $attribute = $this->attributeRepository->findOneByField('admin_name', $code);
        }

        return $attribute;
    }

    /**
     * Get attribute options with optional product count
     */
    public function attributeOptions($rootValue, array $args, GraphQLContext $context)
    {
        $attributeCode = $args['attribute'];
        $withProductCount = $args['withProductCount'] ?? false;

        // Find attribute
        $attribute = $this->attributeRepository->findOneByField('code', $attributeCode);

        if (!$attribute) {
            $attribute = $this->attributeRepository->findOneByField('admin_name', $attributeCode);
        }

        if (!$attribute) {
            return [];
        }

        $options = $this->attributeOptionRepository->findWhere([
            'attribute_id' => $attribute->id
        ]);

        if ($withProductCount) {
            foreach ($options as $option) {
                $productCount = $this->productRepository
                    ->whereHas('attribute_values', function ($q) use ($attribute, $option) {
                        $q->where('attribute_id', $attribute->id)
                            ->where('integer_value', $option->id);
                    })
                    ->count();

                $option->product_count = $productCount;
            }

            // Sort by product count descending
            $options = $options->sortByDesc(function ($option) {
                return $option->product_count ?? 0;
            })->values();
        }

        return $options;
    }

    /**
     * Get product attribute values
     */
    public function productAttributeValues($rootValue, array $args, GraphQLContext $context)
    {
        $productId = $args['productId'];

        $product = $this->productRepository->find($productId);

        if (!$product) {
            return [];
        }

        return $product->attribute_values;
    }

    /**
     * Get attributes for specific product type
     */
    public function attributesForProductType($rootValue, array $args, GraphQLContext $context)
    {
        $productType = $args['type'] ?? 'simple';

        $attributes = $this->attributeRepository
            ->scopeQuery(function ($query) use ($productType) {
                return $query->where(function ($q) use ($productType) {
                    $q->whereJsonContains('product_types', $productType)
                        ->orWhereNull('product_types')
                        ->orWhere('product_types', '[]');
                })
                    ->orderBy('position');
            })
            ->all();

        return $attributes;
    }

    /**
     * Get product attribute values (resolver for Product type)
     */
    public function getProductAttributeValues($product, array $args, GraphQLContext $context)
    {
        return $product->attribute_values;
    }

    /**
     * Get product attribute values by specific attribute code
     */
    public function getProductAttributeValuesByCode($product, array $args, GraphQLContext $context)
    {
        $code = $args['code'];

        $attribute = $this->attributeRepository->findOneByField('code', $code);

        if (!$attribute) {
            return [];
        }

        return $product->attribute_values()
            ->where('attribute_id', $attribute->id)
            ->get();
    }

    /**
     * Get product count for attribute option (resolver for AttributeOption type)
     */
    public function getProductCount($attributeOption, array $args, GraphQLContext $context)
    {
        $attribute = $attributeOption->attribute;

        if (!$attribute) {
            return 0;
        }

        return $this->productRepository
            ->whereHas('attribute_values', function ($q) use ($attribute, $attributeOption) {
                $q->where('attribute_id', $attribute->id)
                    ->where('integer_value', $attributeOption->id);
            })
            ->count();
    }

    /**
     * Get attribute options (resolver for Attribute type)
     */
    public function options($attribute, array $args, GraphQLContext $context)
    {
        return $this->attributeOptionRepository->findWhere([
            'attribute_id' => $attribute->id
        ]);
    }
}

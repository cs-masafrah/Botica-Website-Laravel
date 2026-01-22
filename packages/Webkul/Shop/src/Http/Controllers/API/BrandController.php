<?php

namespace Webkul\Shop\Http\Controllers\API;

use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Shop\Http\Resources\BrandResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Attribute\Repositories\AttributeRepository;

class BrandController extends APIController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AttributeRepository $attributeRepository,
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository
    ) {}

    /**
     * Get all brands with product counts (similar to categories index).
     */
    public function index(): JsonResource
    {
        try {
            $brandAttribute = $this->attributeRepository->findOneByField('code', 'brand')
                ?? $this->attributeRepository->findOneByField('admin_name', 'Brand');

            if (! $brandAttribute) {
                return BrandResource::collection(collect());
            }

            $results = [];
            $locale = app()->getLocale();
            $showOnlyWithProducts = ! request()->boolean('show_all', false);

            if (in_array($brandAttribute->type, ['select', 'multiselect'])) {

                $query = DB::table('attribute_options')
                    ->where('attribute_id', $brandAttribute->id)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('admin_name', 'asc');

                if ($search = request('search')) {
                    $query->where('admin_name', 'like', "%{$search}%");
                }

                $brands = $query->get(['id', 'admin_name', 'image', 'sort_order']);

                foreach ($brands as $brand) {

                    $count = $this->productRepository
                        // ->scopeQuery(fn($q) => $q->where('status', 1))
                        ->whereHas('attribute_values', function ($q) use ($brandAttribute, $brand) {
                            $q->where('attribute_id', $brandAttribute->id)
                                ->where('integer_value', $brand->id);
                        })
                        ->count();

                    if ($count === 0 && $showOnlyWithProducts) {
                        continue;
                    }

                    $results[] = [
                        'id'            => $brand->id,
                        'name'          => $brand->admin_name,
                        'slug'          => Str::slug($brand->admin_name),
                        'image_url'     => $brand->image ? Storage::url($brand->image) : null,
                        'product_count' => $count,
                        'sort_order'    => $brand->sort_order ?? 0,
                        'description'   => null,
                        'status'        => $brand->status ?? 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                        'translations'  => [
                            'name'   => $brand->admin_name,
                            'locale' => $locale,
                        ],
                        'parent_id' => null,
                        'children'  => [],
                        'additional' => [],
                    ];
                }
            } else {
                // Text-based brand attribute
                $query = DB::table('product_attribute_values')
                    ->where('attribute_id', $brandAttribute->id)
                    ->whereNotNull('text_value')
                    ->select('text_value')
                    ->distinct()
                    ->orderBy('text_value');

                if ($search = request('search')) {
                    $query->where('text_value', 'like', "%{$search}%");
                }

                foreach ($query->get() as $value) {
                    $count = $this->productRepository
                        // ->scopeQuery(fn($q) => $q->where('status', 1))
                        ->whereHas('attribute_values', function ($q) use ($brandAttribute, $value) {
                            $q->where('attribute_id', $brandAttribute->id)
                                ->where('text_value', $value->text_value);
                        })
                        ->count();

                    if ($count === 0 && $showOnlyWithProducts) {
                        continue;
                    }

                    $results[] = [
                        'id'            => md5($value->text_value),
                        'name'          => $value->text_value,
                        'slug'          => Str::slug($value->text_value),
                        'image_url'     => null,
                        'product_count' => $count,
                        'sort_order'    => 0,
                        'description'   => null,
                        'status'        => 1,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                        'translations'  => [
                            'name'   => $value->text_value,
                            'locale' => $locale,
                        ],
                        'parent_id' => null,
                        'children'  => [],
                        'additional' => [],
                    ];
                }
            }

            // In your BrandController, after building $results array:
$results = array_map(function ($item) {
    return (object) $item;
}, $results);               
            return BrandResource::collection(collect($results));
        } catch (\Throwable $e) {
           
            \Log::error('BrandController@index', ['error' => $e->getMessage()]);
            return BrandResource::collection(collect());
        }
    }
}
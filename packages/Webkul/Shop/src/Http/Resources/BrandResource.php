<?php

namespace Webkul\Shop\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BrandResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // dd($this);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $this->image_url,
            'product_count' => $this->product_count,
            'sort_order' => $this->sort_order ?? 0,
            'description' => $this->description,
            'status' => $this->status ?? 1,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'translations' => $this->translations ?? [
                'name' => $this->name,
                'locale' => app()->getLocale(),
            ],
            // Add any additional fields you need
            'meta_title' => $this->name . ' Products',
            'meta_description' => 'Browse all ' . $this->name . ' products',
            'meta_keywords' => $this->name . ', products',
        ];
    }
}
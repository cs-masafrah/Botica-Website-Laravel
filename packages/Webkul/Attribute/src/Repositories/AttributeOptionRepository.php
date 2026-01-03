<?php

namespace Webkul\Attribute\Repositories;

use Illuminate\Http\UploadedFile;
use Webkul\Core\Eloquent\Repository;

class AttributeOptionRepository extends Repository
{
    /**
     * Specify Model class name
     */
    public function model(): string
    {
        return 'Webkul\Attribute\Contracts\AttributeOption';
    }

    /**
     * @return \Webkul\Attribute\Contracts\AttributeOption
     */
    public function create(array $data)
    {
        $option = parent::create($data);

        $this->uploadSwatchImage($data, $option->id);
        // Handle image upload before updating
        $data = $this->handleImageUpload($data, $option->id);

        return $option;
    }

    /**
     * @param  int  $id
     * @param  string  $attribute
     * @return \Webkul\Attribute\Contracts\AttributeOption
     */
    public function update(array $data, $id)
    {
        $option = parent::update($data, $id);

        $this->uploadSwatchImage($data, $id);

        // Handle image upload before updating
        $data = $this->handleImageUpload($data, $id);

        return $option;
    }

    /**
     * @param  array  $data
     * @param  int  $optionId
     * @return void
     */
    public function uploadSwatchImage($data, $optionId)
    {
        if (empty($data['swatch_value'])) {
            return;
        }

        if ($data['swatch_value'] instanceof UploadedFile) {
            parent::update([
                'swatch_value' => $data['swatch_value']->store('attribute_option'),
            ], $optionId);
        }
    }

    public function handleImageUpload($data, $optionId = null)
    {
        // Handle new image field upload
        if (!empty($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('attribute_option/images');

            // If updating an existing option, update the image field directly
            if ($optionId) {
                parent::update(['image' => $data['image']], $optionId);
            }
        }

        // Handle image removal (if null or empty string is passed)
        if ($optionId && array_key_exists('image', $data) && empty($data['image'])) {
            parent::update(['image' => null], $optionId);
        }

        return $data;
    }
}
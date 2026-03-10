<?php

namespace Webkul\Reel\Models;

use Webkul\Product\Models\Product as BaseProduct;

class Product extends BaseProduct
{
    /**
     * Get return requests for this product.
     */
    public function reels()
    {
        return $this->hasMany(ReelProxy::modelClass());
    }

    public function getIsSaleableAttribute()
    {
        // Your logic to determine if product is saleable
        return $this->haveSufficientQuantity(1) && $this->status;
    }

    /**
     * Keep the method for backward compatibility
     */
    // public function isSaleable()
    // {
    //     return $this->getIsSaleableAttribute();
    // }
}
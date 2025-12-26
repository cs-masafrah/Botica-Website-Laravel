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
}

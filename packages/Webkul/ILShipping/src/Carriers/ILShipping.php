<?php

namespace Webkul\ILShipping\Carriers;

use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Models\CartShippingRate;
use Webkul\Shipping\Carriers\AbstractShipping;

class ILShipping extends AbstractShipping
{
    /**
     * Shipping method carrier code.
     */
    protected $code = 'ilshipping';

    /**
     * Shipping method code.
     */
    protected $method = 'ilshipping_ilshipping';

    /**
     * Calculate rate for shipping method.
     *
     * @return \Webkul\Checkout\Models\CartShippingRate|false
     */
    public function calculate()
    {
        if (! $this->isAvailable()) {
            return false;
        }

        $cart = Cart::getCart();

        $object = new CartShippingRate;

        $object->carrier = 'ilshipping';
        $object->carrier_title = $this->getConfigData('title');
        $object->method = 'ilshipping_ilshipping';
        $object->method_title = $this->getConfigData('title');
        $object->method_description = $this->getConfigData('description');
        // $object->price = 0;
        // $object->base_price = 0;

        // // Add your shipping calculation logic here
        // $price = $this->getConfigData('default_rate') ?? 0;

        // $object->price = core()->convertPrice($price);
        // $object->base_price = $price;

        // calculate rate - start with base rate
        $baseRate = $this->getConfigData('default_rate');
        $finalRate = $baseRate;

        // express shipping logic - you can customize this
        if ($this->getConfigData('type') === 'per_unit') {
            // calculate per item
            $totalItems = 0;

            foreach ($cart->items as $item) {
                if ($item->product->getTypeInstance()->isStockable()) {
                    $totalItems += $item->quantity;
                }
            }

            $finalRate = $baseRate * $totalItems;
        } else {
            // per order pricing (flat rate)
            $finalRate = $baseRate;
        }

        // set calculated prices
        $object->price = core()->convertPrice($finalRate);
        $object->base_price = $finalRate;


        return $object;
    }
}

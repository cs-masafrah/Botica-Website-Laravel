<?php

namespace Webkul\AbandonedCart\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AbandonedCartEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $cart;
    public $rule;

    public function __construct($cart, $rule)
    {
        $this->cart = $cart;
        $this->rule = $rule;
    }

    public function build()
    {
        $cartItems = json_decode($this->cart->cart_items, true);
        $customerName = $this->cart->customer_first_name . ' ' . $this->cart->customer_last_name;

        return $this->subject($this->rule->email_subject)
            ->view('abandonedcart::shop.emails.abandoned-cart', [
                'cart' => $this->cart,
                'rule' => $this->rule,
                'cartItems' => $cartItems,
                'customerName' => $customerName,
                'recoverUrl' => route('shop.checkout.cart.index')
            ]);
    }
}

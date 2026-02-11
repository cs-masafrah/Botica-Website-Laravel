<?php

namespace Webkul\AbandonedCart\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        try {
            // Validate email address
            if (empty($this->cart->customer_email)) {
                Log::error('Cannot send abandoned cart email: No recipient email address', [
                    'cart_id' => $this->cart->id,
                    'customer_email' => $this->cart->customer_email
                ]);
                throw new \Exception('No recipient email address');
            }

            // cart_items is already an array due to the model's cast
            $cartItems = $this->cart->cart_items ?? [];

            // Log for debugging
            Log::info('Building abandoned cart email', [
                'cart_id' => $this->cart->id,
                'customer_email' => $this->cart->customer_email,
                'cart_items_type' => gettype($cartItems),
                'cart_items_count' => count($cartItems),
                'rule_id' => $this->rule->id,
                'email_subject' => $this->rule->email_subject
            ]);

            $customerName = trim($this->cart->customer_first_name . ' ' . $this->cart->customer_last_name);

            return $this->to($this->cart->customer_email, $customerName)
                ->subject($this->rule->email_subject)
                ->view('abandonedcart::shop.emails.abandoned-cart', [
                    'cart' => $this->cart,
                    'rule' => $this->rule,
                    'cartItems' => $cartItems,
                    'customerName' => $customerName,
                    'recoverUrl' => route('shop.checkout.cart.index')
                ]);
        } catch (\Exception $e) {
            Log::error('Error building abandoned cart email', [
                'error' => $e->getMessage(),
                'cart_id' => $this->cart->id ?? 'N/A',
                'rule_id' => $this->rule->id ?? 'N/A',
                'customer_email' => $this->cart->customer_email ?? 'N/A'
            ]);

            // Re-throw the exception so it can be caught by the caller
            throw $e;
        }
    }
}

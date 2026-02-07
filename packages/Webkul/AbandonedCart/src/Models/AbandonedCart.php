<?php

namespace Webkul\AbandonedCart\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;
use Webkul\Checkout\Models\Cart;
use Webkul\Core\Models\Channel;

class AbandonedCart extends Model
{
    protected $table = 'abandoned_carts';

    protected $fillable = [
        'cart_id',
        'customer_id',
        'customer_email',
        'customer_first_name',
        'customer_last_name',
        'cart_items',
        'cart_total',
        'items_count',
        'is_converted',
        'converted_at',
        'abandoned_at',
        'last_reminder_sent_at',
        'reminder_count',
        'channel_id',
        'locale'
    ];

    protected $casts = [
        'cart_items' => 'array',
        'abandoned_at' => 'datetime',
        'converted_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'cart_total' => 'float',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function getCustomerFullNameAttribute()
    {
        return trim($this->customer_first_name . ' ' . $this->customer_last_name);
    }
}

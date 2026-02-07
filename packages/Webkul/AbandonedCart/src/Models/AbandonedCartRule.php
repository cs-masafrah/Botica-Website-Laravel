<?php

namespace Webkul\AbandonedCart\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedCartRule extends Model
{
    protected $table = 'abandoned_cart_rules';

    protected $fillable = [
        'name',
        'status',
        'abandoned_after_minutes',
        'send_after_minutes',
        'max_reminders',
        'email_template',
        'email_subject',
        'include_coupon',
        'coupon_code',
        'discount_amount',
        'discount_type',
        'channel_ids',
        'customer_group_ids'
    ];

    protected $casts = [
        'channel_ids' => 'array',
        'customer_group_ids' => 'array',
        'include_coupon' => 'boolean',
    ];
}

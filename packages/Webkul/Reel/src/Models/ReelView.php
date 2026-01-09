<?php

namespace Webkul\Reel\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class ReelView extends Model
{
    protected $table = 'reel_views';

    protected $fillable = [
        'reel_id',
        'customer_id',
        'ip_address',
        'user_agent',
        'session_id'
    ];

    public function reel()
    {
        return $this->belongsTo(Reel::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

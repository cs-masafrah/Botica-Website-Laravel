<?php

namespace Webkul\Reel\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Customer\Models\Customer;

class ReelLike extends Model
{
    protected $table = 'reel_likes';

    protected $fillable = [
        'reel_id',
        'customer_id'
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

<?php
// Models/ReelLike.php

namespace Webkul\Reel\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Reel\Contracts\ReelLike as ReelLikeContract;
use Webkul\Customer\Models\Customer;

class ReelLike extends Model implements ReelLikeContract
{
    protected $table = 'reel_likes';

    protected $fillable = [
        'reel_id',
        'customer_id'
    ];

    protected $casts = [
        'reel_id' => 'integer',
        'customer_id' => 'integer',
    ];

    /**
     * Get the reel that owns the like.
     */
    public function reel()
    {
        return $this->belongsTo(ReelProxy::modelClass(), 'reel_id');
    }

    /**
     * Get the customer that owns the like.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

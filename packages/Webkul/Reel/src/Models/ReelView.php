<?php
// Models/ReelView.php

namespace Webkul\Reel\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Reel\Contracts\ReelView as ReelViewContract;
use Webkul\Customer\Models\Customer;

class ReelView extends Model implements ReelViewContract
{
    protected $table = 'reel_views';

    protected $fillable = [
        'reel_id',
        'customer_id',
        'ip_address',
        'user_agent',
        'session_id'
    ];

    protected $casts = [
        'reel_id' => 'integer',
        'customer_id' => 'integer',
    ];

    /**
     * Get the reel that owns the view.
     */
    public function reel()
    {
        return $this->belongsTo(ReelProxy::modelClass(), 'reel_id');
    }

    /**
     * Get the customer that owns the view.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Scope a query to filter by unique views within time period.
     */
    public function scopeUniqueWithin($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope a query to filter by IP address.
     */
    public function scopeByIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope a query to filter by session.
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope a query to filter by customer.
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }
}

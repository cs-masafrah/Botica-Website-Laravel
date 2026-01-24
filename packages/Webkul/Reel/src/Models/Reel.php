<?php

namespace Webkul\Reel\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Models\Product;
use Webkul\Reel\Contracts\Reel as ReelContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Customer\Models\Customer;

class Reel extends Model implements ReelContract
{
    use HasFactory;

    protected $table = 'reels';

    protected $fillable = [
        'title',
        'caption',
        'video_path',
        'thumbnail_path',
        'duration',
        'is_active',
        'sort_order',
        'views_count',
        'likes_count',
        'created_by',
        'product_id'
    ];

    protected $appends = ['video_url', 'thumbnail_url', 'is_liked'];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function likes()
    {
        return $this->hasMany(ReelLike::class, 'reel_id');
    }

    public function views()
    {
        return $this->hasMany(ReelView::class, 'reel_id');
    }

    public function likedByCustomers()
    {
        return $this->belongsToMany(
            Customer::class,
            'reel_likes',
            'reel_id',
            'customer_id'
        )->withTimestamps();
    }

    public function getVideoUrlAttribute()
    {
        return $this->video_path ? Storage::url($this->video_path) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    public function getIsLikedAttribute()
    {
        // Check customer authentication

        // Check customer authentication
        $customer = auth()->guard('customer')->user();
        if (!$customer) {
            // Try other possible guards
            $customer = auth()->guard('api')->user();
        }

        // // Check admin authentication
        $admin = auth()->guard('admin')->user();
        if (!$admin) {
            // Try other possible admin guards
            $admin = auth()->guard('admin-api')->user();
        }

        if (!$customer && !$admin) {
            throw new \Exception('Authentication required to like a reel. Please login as customer or admin.');
        }

        $userId = $customer ? $customer->id : $admin->id;
        $userType = $customer ? 'customer' : 'admin';

        if ($userType == 'customer') {
            return $this->likes()->where('customer_id', $userId)->exists();
        }

        // Check admin authentication
        if ($userType == 'admin') {
            return $this->likes()->where('customer_id', $userId)->exists();
        }

        return false;
    }

    // For GraphQL @method directives
    public function getVideoUrl()
    {
        return $this->video_url;
    }

    public function getThumbnailUrl()
    {
        return $this->thumbnail_url;
    }

    public function getIsLiked()
    {
        return $this->is_liked;
    }

    /**
     * Accessor for product_name logic
     */
    public function getProductNameAttribute()
    {
        if (! $this->product_id) {
            return null;
        }

        $productFlat = DB::table('product_flat')
            ->where('product_id', $this->product_id)
            ->where('locale', app()->getLocale())
            ->first();

        return $productFlat->name ?? null;
    }
}
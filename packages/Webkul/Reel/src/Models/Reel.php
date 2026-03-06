<?php
// Models/Reel.php

namespace Webkul\Reel\Models;

use DB;
use Webkul\Product\Models\Product;
use Webkul\Core\Models\CoreConfig;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Webkul\Reel\Contracts\Reel as ReelContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Customer\Models\Customer;

class Reel extends Model implements ReelContract
{
    use HasFactory;

    protected $table = 'reels';

    protected $fillable = [
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

    protected $appends = ['video_url', 'thumbnail_url', 'is_liked','title', 'caption'];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

     protected $with = ['translations'];
    /**
     * Get the translations for the reel.
     */
    public function translations()
    {
        return $this->hasMany(ReelTranslationProxy::modelClass(), 'reel_id');
    }

    /**
     * Get translation for a specific locale.
     */
    public function translate($locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        $translation = $this->translations()
            ->where('locale', $locale)
            ->first();

        if (!$translation) {
            $translation = $this->translations()
                ->where('locale', core()->getDefaultLocaleCodeFromDefaultChannel())
                ->first();
        }

        return $translation;
    }

    /**
     * Get title attribute from translation.
     */
    public function getTitleAttribute()
    {
        $translation = $this->translate();

        return $translation ? $translation->title : null;
    }

    /**
     * Get caption attribute from translation.
     */
    public function getCaptionAttribute()
    {
        $translation = $this->translate();

        return $translation ? $translation->caption : null;
    }

    /**
     * Get product name with locale support.
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
        $customer = auth()->guard('customer')->user();
        $admin = auth()->guard('admin')->user();

        if (!$customer && !$admin) {
            return false;
        }

        $userId = $customer ? $customer->id : $admin->id;

        return $this->likes()->where('customer_id', $userId)->exists();
    }

    // GraphQL helper methods
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

    public function scopeWithLocale($query, $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->whereHas('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        });
    }
}

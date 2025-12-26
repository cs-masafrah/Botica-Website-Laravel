<?php

namespace Webkul\Reel\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Webkul\Product\Models\ProductProxy;
use Webkul\Product\Models\ProductFlatProxy;
use Webkul\Reel\Contracts\Reel as ReelContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Reel extends Model implements ReelContract
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    protected $appends = ['video_url', 'thumbnail_url'];

    protected $casts = [
        'is_active' => 'boolean',
        'duration' => 'integer',
        'views_count' => 'integer',
        'likes_count' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(ProductProxy::modelClass());
    }

    public function getVideoUrlAttribute()
    {
        return $this->video_path ? Storage::url($this->video_path) : null;
    }

    public function getThumbnailUrlAttribute()
    {
        return $this->thumbnail_path ? Storage::url($this->thumbnail_path) : null;
    }

    // --- ADD THESE NEW METHODS HERE (For GraphQL @method) ---
    public function getVideoUrl()
    {
        return $this->video_url;
    }

    public function getThumbnailUrl()
    {
        return $this->thumbnail_url;
    }

    // Inside packages/Webkul/Reel/src/Models/Reel.php

    /**
     * Accessor for product_name logic
     */
    public function getProductNameAttribute()
    {
        if (! $this->product_id) {
            return null;
        }

        // Getting the name from product_flat based on current locale
        $productFlat = \DB::table('product_flat')
            ->where('product_id', $this->product_id)
            ->where('locale', app()->getLocale())
            ->first();

        return $productFlat->name ?? null;
    }
}
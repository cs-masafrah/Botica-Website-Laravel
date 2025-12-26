<?php

namespace Webkul\Reel\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Reel\Contracts\Reel as ReelContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Webkul\Product\Models\ProductProxy;

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
}

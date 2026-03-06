<?php
// Models/ReelTranslation.php

namespace Webkul\Reel\Models;

use Illuminate\Database\Eloquent\Model;
use Webkul\Reel\Contracts\ReelTranslation as ReelTranslationContract;

class ReelTranslation extends Model implements ReelTranslationContract
{
    protected $table = 'reel_translations';

    protected $fillable = [
        'reel_id',
        'locale',
        'title',
        'caption'
    ];

    public $timestamps = false;

    /**
     * Get the reel that owns the translation.
     */
    public function reel()
    {
        return $this->belongsTo(ReelProxy::modelClass());
    }
}

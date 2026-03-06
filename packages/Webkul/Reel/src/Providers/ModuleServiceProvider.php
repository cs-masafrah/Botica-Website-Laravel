<?php
// Providers/ModuleServiceProvider.php

namespace Webkul\Reel\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \Webkul\Reel\Models\Reel::class,
        \Webkul\Reel\Models\ReelTranslation::class,
        \Webkul\Reel\Models\ReelLike::class,
        \Webkul\Reel\Models\ReelView::class,
    ];
}

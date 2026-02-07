<?php

namespace Webkul\AbandonedCart\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Models.
     *
     * @var array
     */
    protected $models = [
        \Webkul\AbandonedCart\Models\AbandonedCart::class,
        \Webkul\AbandonedCart\Models\AbandonedCartRule::class,
    ];
}

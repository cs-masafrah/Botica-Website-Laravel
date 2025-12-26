<?php

use Illuminate\Support\Facades\Route;
use Webkul\Reel\Http\Controllers\Shop\ReelController;

Route::group(['middleware' => ['web', 'theme', 'locale', 'currency'], 'prefix' => 'reel'], function () {
    Route::get('', [ReelController::class, 'index'])->name('shop.reel.index');
});
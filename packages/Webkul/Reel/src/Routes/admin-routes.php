<?php

use Illuminate\Support\Facades\Route;
use Webkul\Reel\Http\Controllers\Admin\ReelController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin/'], function () {
    Route::controller(ReelController::class)->group(function () {
        // Route::get('', 'index')->name('admin.reel.index');
        Route::resource('reel', ReelController::class, ['as' => 'admin']);
    });
    // Add to your routes file
    Route::group(['prefix' => 'reel', 'as' => 'admin.reel.'], function () {
        Route::get('search-products', [ReelController::class, 'searchProducts'])->name('search_products');
        Route::get('get-products', [ReelController::class, 'getProducts'])->name('get_products');
    });
});
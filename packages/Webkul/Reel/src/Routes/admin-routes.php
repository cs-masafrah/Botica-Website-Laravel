<?php

use Illuminate\Support\Facades\Route;
use Webkul\Reel\Http\Controllers\Admin\ReelController;

Route::group(['middleware' => ['web', 'admin'], 'prefix' => 'admin'], function () {
    // Add custom routes AFTER the resource route
    Route::prefix('reel')->name('admin.reel.')->group(function () {
        Route::get('search-products', [ReelController::class, 'searchProducts'])->name('search_products');
        Route::get('get-products', [ReelController::class, 'getProducts'])->name('get_products');
        Route::post('sort', [ReelController::class, 'sort'])->name('sort');
    });

    // Use ONLY the resource route, not both resource and controller group
    Route::resource('reel', ReelController::class, ['as' => 'admin']);
});

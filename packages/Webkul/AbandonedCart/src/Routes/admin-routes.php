<?php
// packages/Webkul/AbandonedCart/src/Routes/admin-routes.php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web', 'admin']], function () {
    Route::prefix(config('app.admin_url'))->group(function () {
        Route::prefix('abandoned-cart')->group(function () {
            // Rules Routes
            Route::get('rules', [
                'as'   => 'admin.abandoned_cart.rules.index',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@index',
            ]);

            Route::get('rules/create', [
                'as'   => 'admin.abandoned_cart.rules.create',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@create',
            ]);

            Route::post('rules/create', [
                'as'   => 'admin.abandoned_cart.rules.store',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@store',
            ]);

            // For edit page (GET)
            Route::get('rules/edit/{id}', [
                'as'   => 'admin.abandoned_cart.rules.edit',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@edit',
            ]);

            // For update action (POST)
            Route::post('rules/edit/{id}', [
                'as'   => 'admin.abandoned_cart.rules.update',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@update',
            ]);

            // Change from POST to PUT
            Route::put('rules/{id}', [  // Note: removed 'edit/' from URL
                'as'   => 'admin.abandoned_cart.rules.update',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@update',
            ]);



            // FIX: Change to DELETE method OR use POST with @method('DELETE')
            Route::delete('rules/{id}', [
                'as'   => 'admin.abandoned_cart.rules.delete',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\RuleController@destroy',
            ]);

            // Abandoned Carts Routes
            Route::get('carts', [
                'as'   => 'admin.abandoned_cart.carts.index',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\AbandonedCartController@carts',
            ]);

            Route::get('carts/view/{id}', [
                'as'   => 'admin.abandoned_cart.carts.view',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\AbandonedCartController@view',
            ]);

            Route::post('carts/send-email/{id}', [
                'as'   => 'admin.abandoned_cart.carts.send_email',
                'uses' => 'Webkul\AbandonedCart\Http\Controllers\Admin\AbandonedCartController@sendEmail',
            ]);
        });
    });
});

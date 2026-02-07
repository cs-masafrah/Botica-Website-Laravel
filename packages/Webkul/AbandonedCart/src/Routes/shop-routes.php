<?php
// packages/Webkul/AbandonedCart/src/Routes/shop-routes.php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web']], function () {
    Route::get('abandoned-cart/unsubscribe/{token}', [
        'as'   => 'shop.abandoned_cart.unsubscribe',
        'uses' => 'Webkul\AbandonedCart\Http\Controllers\Shop\AbandonedCartController@unsubscribe',
    ]);

    Route::get('abandoned-cart/recover/{token}', [
        'as'   => 'shop.abandoned_cart.recover',
        'uses' => 'Webkul\AbandonedCart\Http\Controllers\Shop\AbandonedCartController@recover',
    ]);
});

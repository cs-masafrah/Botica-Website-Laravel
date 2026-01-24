<?php
use Illuminate\Support\Facades\Route;

Route::get('reels/products', [ReelController::class, 'getProducts'])->name('admin.reel.get_products');
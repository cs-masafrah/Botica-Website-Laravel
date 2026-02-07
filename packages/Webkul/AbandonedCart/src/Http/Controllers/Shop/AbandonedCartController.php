<?php

namespace Webkul\AbandonedCart\Http\Controllers\Shop;

use Illuminate\View\View;
use Webkul\Shop\Http\Controllers\Controller;

class AbandonedCartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('abandonedcart::shop.index');
    }

    
}
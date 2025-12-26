<?php

namespace Webkul\Reel\Http\Controllers\Shop;


use Illuminate\View\View;
use Webkul\Reel\Http\Controllers\Controller;
use Webkul\Reel\Repositories\ReelRepository;
// use Webkul\Shop\Http\Controllers\Controller;


class ReelController extends Controller
{
     public function __construct(
        protected ReelRepository $returnRequestRepository
    ) {}
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('reel::shop.index');
    }
}

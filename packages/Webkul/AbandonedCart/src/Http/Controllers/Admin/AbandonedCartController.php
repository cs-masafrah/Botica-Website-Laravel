<?php
// packages/Webkul/AbandonedCart/src/Http/Controllers/Admin/AbandonedCartController.php

namespace Webkul\AbandonedCart\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\AbandonedCart\Repositories\AbandonedCartRepository;

class AbandonedCartController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AbandonedCartRepository $abandonedCartRepository
    ) {}

    /**
     * Display a listing of abandoned carts.
     *
     * @return \Illuminate\View\View
     */
    public function carts()
    {
        $carts = $this->abandonedCartRepository->all();
        return view('abandonedcart::admin.carts.index', compact('carts'));
    }

    /**
     * View abandoned cart details.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function view($id)
    {
        $cart = $this->abandonedCartRepository->findOrFail($id);
        return view('abandonedcart::admin.carts.view', compact('cart'));
    }

    /**
     * Send manual reminder email.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendEmail($id)
    {
        try {
            $cart = $this->abandonedCartRepository->findOrFail($id);

            // Here you would implement the email sending logic
            // For now, just update the reminder count
            $cart->update([
                'last_reminder_sent_at' => now(),
                'reminder_count' => $cart->reminder_count + 1
            ]);

            session()->flash('success', trans('Email sent successfully'));
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }
}

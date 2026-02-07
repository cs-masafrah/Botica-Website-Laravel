<?php

namespace Webkul\AbandonedCart\Http\Controllers\Admin;

use Webkul\Admin\Http\Controllers\Controller;
use Webkul\AbandonedCart\Repositories\AbandonedCartRuleRepository;
use Illuminate\Http\Request;

class RuleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        protected AbandonedCartRuleRepository $ruleRepository
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $rules = $this->ruleRepository->all();
        return view('abandonedcart::admin.rules.index', compact('rules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('abandonedcart::admin.rules.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'abandoned_after_minutes' => 'required|integer|min:1',
            'send_after_minutes' => 'required|integer|min:1',
            'max_reminders' => 'required|integer|min:1|max:10',
            'email_subject' => 'required',
        ]);

        $data = $request->all();
        $data['channel_ids'] = $request->has('channel_ids') ? $request->channel_ids : null;
        $data['customer_group_ids'] = $request->has('customer_group_ids') ? $request->customer_group_ids : null;

        $this->ruleRepository->create($data);

        session()->flash('success', trans('admin::app.response.create-success', ['name' => 'Rule']));

        return redirect()->route('admin.abandoned_cart.rules.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $rule = $this->ruleRepository->findOrFail($id);
        return view('abandonedcart::admin.rules.edit', compact('rule'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'abandoned_after_minutes' => 'required|integer|min:1',
            'send_after_minutes' => 'required|integer|min:1',
            'max_reminders' => 'required|integer|min:1|max:10',
            'email_subject' => 'required',
        ]);

        $data = $request->all();
        $data['channel_ids'] = $request->has('channel_ids') ? $request->channel_ids : null;
        $data['customer_group_ids'] = $request->has('customer_group_ids') ? $request->customer_group_ids : null;

        $this->ruleRepository->update($data, $id);

        session()->flash('success', 'Rule updated successfully.');

        return redirect()->route('admin.abandoned_cart.rules.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $this->ruleRepository->delete($id);

        session()->flash('success', trans('admin::app.response.delete-success', ['name' => 'Rule']));

        return redirect()->route('admin.abandoned_cart.rules.index');
    }
}

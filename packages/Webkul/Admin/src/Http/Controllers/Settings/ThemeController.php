<?php

namespace Webkul\Admin\Http\Controllers\Settings;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\Admin\DataGrids\Theme\ThemeDataGrid;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\Admin\Http\Requests\MassDestroyRequest;
use Webkul\Admin\Http\Requests\MassUpdateRequest;
use Webkul\Theme\Repositories\ThemeCustomizationRepository;

class ThemeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(public ThemeCustomizationRepository $themeCustomizationRepository) {}

    /**
     * Display a listing resource for the available tax rates.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (request()->ajax()) {
            return datagrid(ThemeDataGrid::class)->process();
        }

        return view('admin::settings.themes.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function store()
    {
        if (request()->has('id')) {
            $this->validate(request(), [
                core()->getRequestedLocaleCode() . '.options.*.image' => 'image|extensions:jpeg,jpg,png,svg,webp',
            ]);

            $theme = $this->themeCustomizationRepository->find(request()->input('id'));

            return $this->themeCustomizationRepository->uploadImage(request()->all(), $theme);
        }

        $validated = $this->validate(request(), [
            'name'       => 'required',
            'sort_order' => 'required|numeric',
            'type'       => 'required|in:product_carousel,category_carousel,static_content,image_carousel,footer_links,services_content,product_by_brand,product_list',
            'channel_id' => 'required|in:' . implode(',', (core()->getAllChannels()->pluck('id')->toArray())),
            'theme_code' => 'required',
        ]);

        Event::dispatch('theme_customization.create.before');

        $theme = $this->themeCustomizationRepository->create($validated);

        Event::dispatch('theme_customization.create.after', $theme);

        return new JsonResponse([
            'redirect_url' => route('admin.settings.themes.edit', $theme->id),
        ]);
    }

    /**
     * Edit the theme
     *
     * @return \Illuminate\View\View
     */
    public function edit(int $id)
    {
        $theme = $this->themeCustomizationRepository->find($id);

        return view('admin::settings.themes.edit', compact('theme'));
    }

    /**
     * Update the specified resource
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(int $id)
    {
        $this->validate(request(), [
            'name'       => 'required',
            'sort_order' => 'required|numeric',
            'type'       => 'required|in:product_carousel,category_carousel,static_content,image_carousel,footer_links,services_content,product_by_brand,product_list',
            'channel_id' => 'required|in:' . implode(',', (core()->getAllChannels()->pluck('id')->toArray())),
            'theme_code' => 'required',
        ]);

        $locale = request('locale');

        $data = request()->only(
            'locale',
            'type',
            'name',
            'sort_order',
            'channel_id',
            'theme_code',
            'status',
            $locale
        );

        Event::dispatch('theme_customization.update.before', $id);

        $data['status'] = request()->input('status') == 'on';

        $theme = $this->themeCustomizationRepository->update($data, $id);

        Event::dispatch('theme_customization.update.after', $theme);

        session()->flash('success', trans('admin::app.settings.themes.update-success'));

        return redirect()->route('admin.settings.themes.index');
    }

    /**
     * Delete a specified theme.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        Event::dispatch('theme_customization.delete.before', $id);

        $this->themeCustomizationRepository->delete($id);

        Storage::deleteDirectory('theme/' . $id);

        Event::dispatch('theme_customization.delete.after', $id);

        return new JsonResponse([
            'message' => trans('admin::app.settings.themes.delete-success'),
        ], 200);
    }

    public function massUpdate(MassUpdateRequest $massUpdateRequest): JsonResponse
    {
        $selectedThemeIds = $massUpdateRequest->input('indices');

        $this->themeCustomizationRepository->massUpdateStatus([
            'status' => $massUpdateRequest->input('value'),
        ], $selectedThemeIds);

        return new JsonResponse([
            'message' => trans('admin::app.settings.themes.update-success'),
        ]);
    }

    public function massDestroy(MassDestroyRequest $massDestroyRequest): JsonResponse
    {
        $selectedThemeIds = $massDestroyRequest->input('indices');

        foreach ($selectedThemeIds as $themeId) {
            $this->themeCustomizationRepository->delete($themeId);
        }

        return new JsonResponse([
            'message' => trans('admin::app.settings.themes.update-success'),
        ]);
    }

    public function sort(Request $request)
    {
        \Log::info('Sort request received:', $request->all());

        try {
            // Get the sort_order data (it might be a JSON string)
            $sortOrder = $request->input('sort_order');

            // If it's a string, decode it
            if (is_string($sortOrder)) {
                $sortOrder = json_decode($sortOrder, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON format for sort_order');
                }
            }

            \Log::info('Decoded sort order:', $sortOrder);

            // Validate the data
            $validator = \Validator::make(['sort_order' => $sortOrder], [
                'sort_order' => 'required|array',
                'sort_order.*.id' => 'required|exists:theme_customizations,id',
                'sort_order.*.sort_order' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                \Log::error('Validation failed:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            \Log::info('Processing sort order updates...');

            foreach ($sortOrder as $item) {
                \Log::info("Updating theme ID {$item['id']} to sort order {$item['sort_order']}");

                // Use direct DB update to avoid validation issues
                \DB::table('theme_customizations')
                    ->where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            \Log::info('Sort order updates completed successfully');

            return response()->json([
                'success' => true,
                'message' => trans('admin::app.settings.themes.index.sort-order-saved')
            ]);
        } catch (\Exception $e) {
            \Log::error('Sort order update failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
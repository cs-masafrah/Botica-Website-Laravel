<?php
// Http/Controllers/Admin/ReelController.php

namespace Webkul\Reel\Http\Controllers\Admin;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Webkul\Reel\Models\Reel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Webkul\Reel\Http\Controllers\Controller;
use Webkul\Reel\Repositories\ReelRepository;
use Webkul\Reel\DataGrids\Admin\ReelDataGrid;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Core\Repositories\LocaleRepository;

class ReelController extends Controller
{
    protected $reelRepository;
    protected $productRepository;
    protected $localeRepository;

    public function __construct(
        ReelRepository $reelRepository,
        ProductRepository $productRepository,
        LocaleRepository $localeRepository
    ) {
        $this->reelRepository = $reelRepository;
        $this->productRepository = $productRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Display a listing of the reels.
     */
    public function index(): View|JsonResponse
    {
        if (! bouncer()->hasPermission('reel') && ! bouncer()->hasPermission('reel.list')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        if (request()->ajax()) {
            return datagrid(ReelDataGrid::class)->process();
        }

        // Get all active locales from the system
        $locales = $this->localeRepository->all();

        return view('reel::admin.index', compact('locales'));
    }

    /**
     * Show the form for creating a new reel.
     */
    public function create(): View
    {
        if (! bouncer()->hasPermission('reel.create')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $products = $this->productRepository->all();
        $locales = $this->localeRepository->all();

        return view('reel::admin.create', compact('products', 'locales'));
    }

    /**
     * Store a newly created reel.
     */
    public function store(Request $request): JsonResponse
    {
        if (! bouncer()->hasPermission('reel.create')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $request->validate([
            'product_id'   => 'nullable|exists:products,id',
            'video'        => 'required|mimes:mp4,mov,webm|max:51200',
            'thumbnail'    => 'nullable|image|max:2048',
            'duration'     => 'nullable|integer',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
        ]);

        // Validate translations
        $locales = $this->localeRepository->all();
        foreach ($locales as $locale) {
            $request->validate([
                $locale->code . '.title' => 'required|string|max:255',
                $locale->code . '.caption' => 'nullable|string',
            ]);
        }

        /** Store video */
        $videoPath = $request->file('video')->store('reels/videos', 'public');

        /** Store thumbnail */
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('reels/thumbnails', 'public');
        }

        // Calculate sort order if not provided
        $sortOrder = $request->input('sort_order', 0);
        if ($sortOrder == 0) {
            $maxSortOrder = \DB::table('reels')->max('sort_order');
            $sortOrder = ($maxSortOrder ?: 0) + 1;
        }

        $data = [
            'video_path'     => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'duration'       => $request->input('duration'),
            'sort_order'     => $sortOrder,
            'is_active'      => $request->boolean('is_active', true),
            'product_id'     => $request->input('product_id'),
            'created_by'     => auth()->guard('admin')->id(),
        ];

        // Add translations
        foreach ($locales as $locale) {
            if ($request->has($locale->code)) {
                $data[$locale->code] = [
                    'title'   => $request->input($locale->code . '.title'),
                    'caption' => $request->input($locale->code . '.caption'),
                ];
            }
        }

        $this->reelRepository->create($data);

        return new JsonResponse([
            'message' => trans('reel::app.admin.reels.messages.create-success'),
        ]);
    }

    /**
     * Show the form for editing the specified reel.
     */
    public function edit(Reel $reel)
    {
        if (! bouncer()->hasPermission('reel.edit')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $products = $this->productRepository->all();
        $locales = $this->localeRepository->all();

        // Load translations
        $reel->load('translations');

        return response()->json([
            'data' => $reel,
            'products' => $products,
            'locales' => $locales,
        ]);
    }

    /**
     * Update the specified reel.
     */
    public function update(Request $request, Reel $reel)
    {
        if (! bouncer()->hasPermission('reel.edit')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $request->validate([
            'product_id'   => 'nullable|exists:products,id',
            'video'        => 'nullable|mimes:mp4,mov,webm|max:51200',
            'thumbnail'    => 'nullable|image|max:2048',
            'duration'     => 'nullable|integer',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
        ]);

        // Validate translations
        $locales = $this->localeRepository->all();
        foreach ($locales as $locale) {
            $request->validate([
                $locale->code . '.title' => 'required|string|max:255',
                $locale->code . '.caption' => 'nullable|string',
            ]);
        }

        $data = [
            'duration'       => $request->input('duration', $reel->duration),
            'sort_order'     => $request->input('sort_order', $reel->sort_order),
            'is_active'      => $request->boolean('is_active', $reel->is_active),
            'product_id'     => $request->input('product_id', $reel->product_id),
        ];

        /** Replace video if uploaded */
        if ($request->hasFile('video')) {
            if ($reel->video_path) {
                Storage::disk('public')->delete($reel->video_path);
            }

            $data['video_path'] = $request->file('video')
                ->store('reels/videos', 'public');
        }

        /** Replace thumbnail if uploaded */
        if ($request->hasFile('thumbnail')) {
            if ($reel->thumbnail_path) {
                Storage::disk('public')->delete($reel->thumbnail_path);
            }

            $data['thumbnail_path'] = $request->file('thumbnail')
                ->store('reels/thumbnails', 'public');
        }

        // Add translations
        foreach ($locales as $locale) {
            if ($request->has($locale->code)) {
                $data[$locale->code] = [
                    'title'   => $request->input($locale->code . '.title'),
                    'caption' => $request->input($locale->code . '.caption'),
                ];
            }
        }

        $this->reelRepository->update($data, $reel->id);

        return new JsonResponse([
            'message' => trans('reel::app.admin.reels.messages.update-success'),
        ]);
    }

    /**
     * Remove the specified reel (soft delete).
     */
    public function destroy(Reel $reel)
    {
        if (! bouncer()->hasPermission('reel.delete')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        // Cleanup files
        if ($reel->video_path) {
            Storage::disk('public')->delete($reel->video_path);
        }
        if ($reel->thumbnail_path) {
            Storage::disk('public')->delete($reel->thumbnail_path);
        }

        $this->reelRepository->delete($reel->id);

        return new JsonResponse([
            'message' => trans('reel::app.admin.reels.messages.delete-success'),
        ]);
    }

    /**
     * Mass delete reels.
     */
    public function massDestroy(Request $request): JsonResponse
    {
        if (! bouncer()->hasPermission('reel.delete')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $request->validate([
            'indices' => 'required|array',
            'indices.*' => 'integer|exists:reels,id',
        ]);

        try {
            foreach ($request->indices as $reelId) {
                $reel = $this->reelRepository->find($reelId);

                if ($reel) {
                    // Cleanup files
                    if ($reel->video_path) {
                        Storage::disk('public')->delete($reel->video_path);
                    }
                    if ($reel->thumbnail_path) {
                        Storage::disk('public')->delete($reel->thumbnail_path);
                    }

                    $this->reelRepository->delete($reelId);
                }
            }

            return new JsonResponse([
                'message' => trans('reel::app.admin.reels.messages.mass-delete-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mass update reel status.
     */
    public function massUpdateStatus(Request $request): JsonResponse
    {
        if (! bouncer()->hasPermission('reel.edit')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $request->validate([
            'indices' => 'required|array',
            'indices.*' => 'integer|exists:reels,id',
            'value' => 'required|in:0,1',
        ]);

        try {
            foreach ($request->indices as $reelId) {
                $this->reelRepository->update([
                    'is_active' => $request->value
                ], $reelId);
            }

            return new JsonResponse([
                'message' => trans('reel::app.admin.reels.messages.mass-update-success'),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save sort order from drag & drop.
     */
    public function sort(Request $request): JsonResponse
    {
        if (! bouncer()->hasPermission('reel.edit')) {
            return response()->json([
                'success' => false,
                'message' => trans('reel::app.admin.reels.messages.unauthorized')
            ], 401);
        }

        try {
            $sortOrder = $request->input('sort_order');

            if (is_string($sortOrder)) {
                $sortOrder = json_decode($sortOrder, true);
            }

            $validator = \Validator::make(['sort_order' => $sortOrder], [
                'sort_order' => 'required|array',
                'sort_order.*.id' => 'required|exists:reels,id',
                'sort_order.*.sort_order' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                ], 422);
            }

            foreach ($sortOrder as $item) {
                \DB::table('reels')
                    ->where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => trans('reel::app.admin.reels.messages.sort-order-saved')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products for dropdown.
     */
    public function getProducts()
    {
        try {
            $products = $this->productRepository->all();

            return response()->json([
                'success' => true,
                'data' => $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get locales for dropdown.
     */
    public function getLocales()
    {
        try {
            $locales = $this->localeRepository->all();

            return response()->json([
                'success' => true,
                'data' => $locales->map(function ($locale) {
                    return [
                        'id' => $locale->id,
                        'code' => $locale->code,
                        'name' => $locale->name,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}

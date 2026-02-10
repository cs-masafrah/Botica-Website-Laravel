<?php

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

class ReelController extends Controller
{
    /**
     * Create a new controller instance.
     */
    protected $reelRepository;
    protected $productRepository;

    public function __construct(ReelRepository $reelRepository, ProductRepository $productRepository)
    {
        $this->reelRepository = $reelRepository;
        $this->productRepository = $productRepository;
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

        return view('reel::admin.index');
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

        return view('reel::admin.create', compact('products'));
    }

    /**
     * Store a newly created reel.
     */
    public function store(Request $request): JsonResponse
    {
        if (! bouncer()->hasPermission('reel.create')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'caption'      => 'nullable|string',
            'product_id'   => 'nullable|exists:products,id',
            'video'        => 'required|mimes:mp4,mov,webm|max:51200',
            'thumbnail'    => 'nullable|image|max:2048',
            'duration'     => 'nullable|integer',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
        ]);

        /** Store video */
        $videoPath = $request->file('video')->store('reels/videos', 'public');

        /** Store thumbnail */
        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('reels/thumbnails', 'public');
        }

        // Calculate sort order if not provided
        $sortOrder = $validated['sort_order'] ?? 0;
        if ($sortOrder == 0) {
            // Get the maximum sort_order from database
            $maxSortOrder = \DB::table('reels')->max('sort_order');
            $sortOrder = ($maxSortOrder ?: 0) + 1;
        }

        $this->reelRepository->create([
            'title'          => $validated['title'],
            'caption'        => $validated['caption'] ?? null,
            'product_id'     => $validated['product_id'] ?? null,
            'video_path'     => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'duration'       => $validated['duration'] ?? null,
            'sort_order'     => $sortOrder, // Use calculated sort order
            'is_active'      => $validated['is_active'] ?? true,
            'created_by'     => auth()->guard('admin')->id(),
        ]);

        return new JsonResponse([
            'message' => trans('reel::app.admin.reels.messages.create-success'),
        ]);
    }

    /**
     * Show the specified reel.
     */
    public function show(Reel $reel)
    {
        if (! bouncer()->hasPermission('reel.view')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        return view('reel::admin.show', compact('reel'));
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

        return response()->json([
            'data' => $reel,
            'products' => $products,
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

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'caption'      => 'nullable|string',
            'product_id'   => 'nullable|exists:products,id',
            'video'        => 'nullable|mimes:mp4,mov,webm|max:51200',
            'thumbnail'    => 'nullable|image|max:2048',
            'duration'     => 'nullable|integer',
            'sort_order'   => 'nullable|integer',
            'is_active'    => 'boolean',
        ]);

        $data = $validated;

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

        $this->reelRepository->delete($reel->id);

        return new JsonResponse([
            'message' => trans('reel::app.admin.reels.messages.delete-success'),
        ]);
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
            // Get the sort_order data
            $sortOrder = $request->input('sort_order');

            // If it's a string, decode it
            if (is_string($sortOrder)) {
                $sortOrder = json_decode($sortOrder, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Invalid JSON format for sort_order');
                }
            }

            // Validate the data
            $validator = \Validator::make(['sort_order' => $sortOrder], [
                'sort_order' => 'required|array',
                'sort_order.*.id' => 'required|exists:reels,id',
                'sort_order.*.sort_order' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . $validator->errors()->first(),
                    'errors' => $validator->errors()
                ], 422);
            }

            // Process updates
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
            // Get all products with id and name
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
}

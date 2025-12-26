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
        // dd(trans('reel::app.admin.reels.messages.create-success'));
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
        $products = $this->productRepository->all();

        return view('reel::admin.create', compact('products'));
    }


    /**
     * Store a newly created reel.
     */
    public function store(Request $request): JsonResponse
    {
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

        $this->reelRepository->create([
            'title'          => $validated['title'],
            'caption'        => $validated['caption'] ?? null,
            'product_id'     => $validated['product_id'] ?? null,
            'video_path'     => $videoPath,
            'thumbnail_path' => $thumbnailPath,
            'duration'       => $validated['duration'] ?? null,
            'sort_order'     => $validated['sort_order'] ?? 0,
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
        // $reel = $this->reelRepository->findOrFail($id);

        return view('reel::admin.show', compact('reel'));
    }

    /**
     * Show the form for editing the specified reel.
     */
    public function edit(Reel $reel)
    {
        // $reel = $this->reelRepository->findOrFail($id);
        $products = $this->productRepository->all();
        // Map video URL fully qualified
        $reel->video_url = $reel->video_path ? asset('storage/' . $reel->video_path) : null;
        // Map video URL fully qualified
        $reel->thumbnail_path = $reel->thumbnail_path ? asset('storage/' . $reel->thumbnail_path) : null;
        return response()->json([
            'data' => $reel,
            'products' => $products,
        ]);
    }
    public function update(Request $request, Reel $reel)
    {
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
        $this->reelRepository->delete($reel->id);

        return new JsonResponse([
            'message' => trans('reel::app.admin.reels.messages.delete-success'),
        ]);
    }
}
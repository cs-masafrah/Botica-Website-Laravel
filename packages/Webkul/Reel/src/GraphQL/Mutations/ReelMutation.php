<?php

namespace Webkul\Reel\GraphQL\Mutations;

use Webkul\Reel\Repositories\ReelRepository;
use Illuminate\Support\Facades\Storage;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class ReelMutation
{
    protected $reelRepository;

    public function __construct(ReelRepository $reelRepository)
    {
        $this->reelRepository = $reelRepository;
    }

    /**
     * Custom Authorization Logic for JWT/admin-api
     */
    protected function authorize($permission)
    {
        $admin = auth()->guard('admin-api')->user();

        // 1. Check Authentication
        if (! $admin) {
            throw new \Exception(trans('reel::app.admin.reels.messages.unauthorized') ?? 'Unauthenticated.');
        }

        // 2. Manual Permission Check via Role Array
        $permissions = $admin->role->permissions ?? [];
        $hasPermission = ($admin->role->permission_type === 'all') || in_array($permission, $permissions);

        if (! $hasPermission) {
            throw new \Exception(trans('reel::app.admin.reels.messages.unauthorized') ?? "Unauthorized: Missing {$permission} permission.");
        }

        return $admin;
    }

    /**
     * Create Reel
     */
    public function create($root, array $args, GraphQLContext $context)
    {
        try {
            $admin = $this->authorize('reel.create');

            $input = $args['input'];

            $data = [
                'title'          => $input['title'],
                'caption'        => $input['caption'] ?? null,
                'product_id'     => $input['product_id'] ?? null,
                'video_path'     => $input['video_path'] ?? null,
                'thumbnail_path' => $input['thumbnail_path'] ?? null,
                'duration'       => $input['duration'] ?? null,
                'sort_order'     => $input['sort_order'] ?? 0,
                'is_active'      => $input['is_active'] ?? true,
                'created_by'     => $admin->id,
            ];

            $reel = $this->reelRepository->create($data);

            return [
                'success' => true,
                'message' => trans('reel::app.admin.reels.messages.create-success'),
                'reel'    => $reel,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'reel'    => null,
            ];
        }
    }

    /**
     * Update Reel
     */
    public function update($root, array $args, GraphQLContext $context)
    {
        try {
            $this->authorize('reel.edit');

            $id = $args['id'];
            $input = $args['input'];

            $reel = $this->reelRepository->findOrFail($id);

            $data = [
                'title'      => $input['title'] ?? $reel->title,
                'caption'    => $input['caption'] ?? $reel->caption,
                'product_id' => $input['product_id'] ?? $reel->product_id,
                'duration'   => $input['duration'] ?? $reel->duration,
                'sort_order' => $input['sort_order'] ?? $reel->sort_order,
                'is_active'  => $input['is_active'] ?? $reel->is_active,
            ];

            if (isset($input['video_path'])) {
                if ($reel->video_path) Storage::disk('public')->delete($reel->video_path);
                $data['video_path'] = $input['video_path'];
            }

            if (isset($input['thumbnail_path'])) {
                if ($reel->thumbnail_path) Storage::disk('public')->delete($reel->thumbnail_path);
                $data['thumbnail_path'] = $input['thumbnail_path'];
            }

            $updatedReel = $this->reelRepository->update($data, $id);

            return [
                'success' => true,
                'message' => trans('reel::app.admin.reels.messages.update-success'),
                'reel'    => $updatedReel,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'reel'    => null,
            ];
        }
    }

    /**
     * Delete Reel
     */
    public function delete($root, array $args, GraphQLContext $context)
    {
        try {
            $this->authorize('reel.delete');

            $id = $args['id'];
            $reel = $this->reelRepository->findOrFail($id);

            // Cleanup files before deleting record
            if ($reel->video_path) Storage::disk('public')->delete($reel->video_path);
            if ($reel->thumbnail_path) Storage::disk('public')->delete($reel->thumbnail_path);

            $this->reelRepository->delete($id);

            return [
                'success' => true,
                'message' => trans('reel::app.admin.reels.messages.delete-success'),
                'reel'    => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'reel'    => null,
            ];
        }
    }
}
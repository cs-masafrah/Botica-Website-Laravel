<?php

namespace Webkul\Reel\GraphQL\Mutations;

use Webkul\Reel\Repositories\ReelRepository;
use Illuminate\Support\Facades\Storage;

class ReelMutation
{
    public function __construct(protected ReelRepository $reelRepository) {}

    public function delete($rootValue, array $args)
    {
        $reel = $this->reelRepository->find($args['id']);

        if (!$reel) {
            return ['success' => false, 'message' => 'Reel not found.'];
        }

        try {
            // Delete files from storage
            if ($reel->video_path) Storage::disk('public')->delete($reel->video_path);
            if ($reel->thumbnail_path) Storage::disk('public')->delete($reel->thumbnail_path);

            $this->reelRepository->delete($args['id']);

            return ['success' => true, 'message' => trans('reel::app.admin.reels.messages.delete-success')];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
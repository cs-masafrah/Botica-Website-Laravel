<?php

namespace Webkul\Reel\GraphQL\Resolvers;

use Webkul\Reel\Repositories\ReelRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ReelMutationResolver
{
    protected $reelRepository;

    public function __construct(ReelRepository $reelRepository)
    {
        $this->reelRepository = $reelRepository;
    }

    public function create($root, array $args, $context, $info)
    {
        if (! bouncer()->hasPermission('reel.create')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $input = $args['input'];

        // Validate inside resolver or you can rely on GraphQL input validation
        // Handle file upload (assumes base64 string or separate file upload process)
        if (isset($input['video'])) {
            $input['video_path'] = $this->storeFile($input['video'], 'reels/videos');
            unset($input['video']);
        }

        if (isset($input['thumbnail'])) {
            $input['thumbnail_path'] = $this->storeFile($input['thumbnail'], 'reels/thumbnails');
            unset($input['thumbnail']);
        }

        $input['created_by'] = auth()->guard('admin')->id();

        $reel = $this->reelRepository->create($input);

        return [
            'success' => true,
            'message' => trans('reel::app.admin.reels.messages.create-success'),
            'reel'    => $reel,
        ];
    }

    public function update($root, array $args, $context, $info)
    {
        if (! bouncer()->hasPermission('reel.edit')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $id = $args['id'];
        $input = $args['input'];

        $reel = $this->reelRepository->find($id);

        if (!$reel) {
            abort(404, 'Reel not found.');
        }

        if (isset($input['video'])) {
            if ($reel->video_path) {
                Storage::disk('public')->delete($reel->video_path);
            }
            $input['video_path'] = $this->storeFile($input['video'], 'reels/videos');
            unset($input['video']);
        }

        if (isset($input['thumbnail'])) {
            if ($reel->thumbnail_path) {
                Storage::disk('public')->delete($reel->thumbnail_path);
            }
            $input['thumbnail_path'] = $this->storeFile($input['thumbnail'], 'reels/thumbnails');
            unset($input['thumbnail']);
        }

        $this->reelRepository->update($input, $id);

        return [
            'success' => true,
            'message' => trans('reel::app.admin.reels.messages.update-success'),
        ];
    }

    public function delete($root, array $args, $context, $info)
    {
        if (! bouncer()->hasPermission('reel.delete')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $id = $args['id'];
        $this->reelRepository->delete($id);

        return [
            'success' => true,
            'message' => trans('reel::app.admin.reels.messages.delete-success'),
        ];
    }

    protected function storeFile($file, $folder)
    {
        // $file can be a base64 string or uploaded file object, depends on client

        // Here, example if file is base64 string:
        if (is_string($file) && preg_match('/^data:.*;base64,/', $file)) {
            $fileData = base64_decode(preg_replace('#^data:.*;base64,#', '', $file));
            $fileName = uniqid() . '.mp4'; // or jpg, png etc based on usage
            Storage::disk('public')->put($folder . '/' . $fileName, $fileData);
            return $folder . '/' . $fileName;
        }

        // If file is uploaded as UploadedFile instance:
        if ($file instanceof UploadedFile) {
            return $file->store($folder, 'public');
        }

        // Otherwise throw error or handle accordingly
        abort(422, 'Invalid file upload');
    }
}
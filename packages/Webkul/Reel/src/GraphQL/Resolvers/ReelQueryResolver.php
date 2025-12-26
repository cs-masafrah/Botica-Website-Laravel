<?php

namespace Webkul\Reel\GraphQL\Resolvers;

use Webkul\Reel\Repositories\ReelRepository;
use Illuminate\Support\Facades\Auth;

class ReelQueryResolver
{
    protected $reelRepository;

    public function __construct(ReelRepository $reelRepository)
    {
        $this->reelRepository = $reelRepository;
    }

    public function list($root, array $args, $context, $info)
    {
        if (! bouncer()->hasPermission('reel.list') && ! bouncer()->hasPermission('reel')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        $perPage = $args['input']['per_page'] ?? 10;
        $page = $args['input']['page'] ?? 1;

        $query = $this->reelRepository->query();

        // Apply filters if any
        if (!empty($args['input']['title'])) {
            $query->where('title', 'like', '%' . $args['input']['title'] . '%');
        }

        if (isset($args['input']['is_active'])) {
            $query->where('is_active', $args['input']['is_active']);
        }

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function find($root, array $args, $context, $info)
    {
        if (! bouncer()->hasPermission('reel.view')) {
            abort(401, trans('reel::app.admin.reels.messages.unauthorized'));
        }

        return $this->reelRepository->find($args['id']);
    }
}
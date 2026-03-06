<?php

namespace Webkul\Reel\GraphQL\Resolvers;

use Webkul\Reel\Repositories\ReelRepository;
use Webkul\Core\Repositories\LocaleRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ReelQueryResolver
{
    protected $reelRepository;
    protected $localeRepository;

    public function __construct(
        ReelRepository $reelRepository,
        LocaleRepository $localeRepository
    ) {
        $this->reelRepository = $reelRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * Get paginated list of reels with translations
     */
    public function list($root, array $args, $context, $info)
    {
        $input = $args['input'] ?? [];

        $perPage = $input['per_page'] ?? 10;
        $page = $input['page'] ?? 1;

        // Get locale from input or use current app locale
        $locale = $input['locale'] ?? app()->getLocale();

        // Start building query using the repository's model
        $query = $this->reelRepository->getModel()->newQuery();

        // Eager load translations
        $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);

        // Filter by translations existence
        $query->whereHas('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        });

        // Apply title filter
        if (!empty($input['title'])) {
            $query->whereHas('translations', function ($q) use ($input, $locale) {
                $q->where('locale', $locale)
                  ->where('title', 'LIKE', '%' . $input['title'] . '%');
            });
        }

        // Apply active status filter
        if (isset($input['is_active'])) {
            $query->where('is_active', $input['is_active']);
        }

        // Apply sorting
        $sortBy = $input['sort_by'] ?? 'sort_order';
        $sortOrder = isset($input['sort_order']) && strtoupper($input['sort_order']) === 'DESC' ? 'DESC' : 'ASC';

        $query->orderBy($sortBy, $sortOrder);

        // Get paginated results using Laravel's paginator
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => [
                'count' => $paginator->count(),
                'currentPage' => $paginator->currentPage(),
                'firstItem' => $paginator->firstItem(),
                'hasMorePages' => $paginator->hasMorePages(),
                'lastItem' => $paginator->lastItem(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ];
    }

    /**
     * Find a single reel by ID with translations
     */
    public function find($root, array $args, $context, $info)
    {
        $id = $args['id'];
        $input = $args['input'] ?? [];

        $locale = $input['locale'] ?? app()->getLocale();

        $reel = $this->reelRepository
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }])
            ->find($id);

        return $reel;
    }

    /**
     * Get reels by product ID
     */
    public function byProduct($root, array $args, $context, $info)
    {
        $productId = $args['product_id'];
        $input = $args['input'] ?? [];

        $locale = $input['locale'] ?? app()->getLocale();
        $perPage = $input['per_page'] ?? 10;
        $page = $input['page'] ?? 1;

        $query = $this->reelRepository->getModel()->newQuery()
            ->with(['translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            }])
            ->where('product_id', $productId)
            ->where('is_active', true)
            ->whereHas('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            })
            ->orderBy('sort_order');

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => $paginator->items(),
            'paginatorInfo' => [
                'count' => $paginator->count(),
                'currentPage' => $paginator->currentPage(),
                'firstItem' => $paginator->firstItem(),
                'hasMorePages' => $paginator->hasMorePages(),
                'lastItem' => $paginator->lastItem(),
                'lastPage' => $paginator->lastPage(),
                'perPage' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        ];
    }

    /**
     * Get all available locales for reels
     */
    public function getLocales($root, array $args, $context, $info)
    {
        return $this->localeRepository->all()->map(function ($locale) {
            return [
                'id' => $locale->id,
                'code' => $locale->code,
                'name' => $locale->name,
                'direction' => $locale->direction,
                'is_default' => $locale->is_default,
            ];
        });
    }

    /**
     * Get reel analytics
     */
    public function analytics($root, array $args, $context, $info)
    {
        $reel = $this->reelRepository->find($args['id']);

        if (!$reel) {
            return [
                'success' => false,
                'message' => 'Reel not found',
                'reel' => null,
                'analytics' => null
            ];
        }

        $totalLikes = $reel->likes()->count();
        $totalViews = $reel->views()->count();

        $uniqueViewers = $reel->views()
            ->select(DB::raw('COUNT(DISTINCT COALESCE(customer_id, ip_address, session_id)) as count'))
            ->value('count') ?? 0;

        $engagementRate = $totalViews > 0 ? round(($totalLikes / $totalViews) * 100, 2) : 0;

        return [
            'success' => true,
            'message' => 'Analytics fetched successfully',
            'reel' => $reel,
            'analytics' => [
                'total_likes' => $totalLikes,
                'total_views' => $totalViews,
                'unique_viewers' => $uniqueViewers,
                'engagement_rate' => $engagementRate,
            ]
        ];
    }

    /**
     * Helper method to create paginator info
     */
    protected function getPaginatorInfo(LengthAwarePaginator $paginator): array
    {
        return [
            'count' => $paginator->count(),
            'currentPage' => $paginator->currentPage(),
            'firstItem' => $paginator->firstItem(),
            'hasMorePages' => $paginator->hasMorePages(),
            'lastItem' => $paginator->lastItem(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}

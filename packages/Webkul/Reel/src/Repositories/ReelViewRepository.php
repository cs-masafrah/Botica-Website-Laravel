<?php
// Repositories/ReelViewRepository.php

namespace Webkul\Reel\Repositories;

use Webkul\Core\Eloquent\Repository;

class ReelViewRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Reel\Contracts\ReelView';
    }

    /**
     * Check if view should be counted (prevent duplicate views).
     */
    public function shouldCountView($reelId, $customerId = null, $ip = null, $sessionId = null, $hours = 24): bool
    {
        $query = $this->model->where('reel_id', $reelId);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        } elseif ($ip && $sessionId) {
            $query->where(function ($q) use ($ip, $sessionId) {
                $q->where('ip_address', $ip)
                  ->orWhere('session_id', $sessionId);
            });
        }

        return !$query->where('created_at', '>=', now()->subHours($hours))->exists();
    }

    /**
     * Get unique viewers count for a reel.
     */
    public function getUniqueViewersCount($reelId): int
    {
        return $this->model
            ->where('reel_id', $reelId)
            ->selectRaw('COUNT(DISTINCT COALESCE(customer_id, ip_address, session_id)) as count')
            ->value('count') ?? 0;
    }

    /**
     * Get views count for a reel.
     */
    public function getViewsCount($reelId): int
    {
        return $this->model
            ->where('reel_id', $reelId)
            ->count();
    }

    /**
     * Get views analytics for a reel.
     */
    public function getAnalytics($reelId): array
    {
        $totalViews = $this->getViewsCount($reelId);
        $uniqueViewers = $this->getUniqueViewersCount($reelId);

        $viewsByDay = $this->model
            ->where('reel_id', $reelId)
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'total_views' => $totalViews,
            'unique_viewers' => $uniqueViewers,
            'views_by_day' => $viewsByDay,
        ];
    }
}

<?php
// Repositories/ReelLikeRepository.php

namespace Webkul\Reel\Repositories;

use Webkul\Core\Eloquent\Repository;

class ReelLikeRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Reel\Contracts\ReelLike';
    }

    /**
     * Check if customer already liked a reel.
     */
    public function hasLiked($reelId, $customerId): bool
    {
        return $this->model
            ->where('reel_id', $reelId)
            ->where('customer_id', $customerId)
            ->exists();
    }

    /**
     * Get likes count for a reel.
     */
    public function getLikesCount($reelId): int
    {
        return $this->model
            ->where('reel_id', $reelId)
            ->count();
    }

    /**
     * Delete like by customer and reel.
     */
    public function deleteLike($reelId, $customerId): bool
    {
        return $this->model
            ->where('reel_id', $reelId)
            ->where('customer_id', $customerId)
            ->delete() > 0;
    }
}

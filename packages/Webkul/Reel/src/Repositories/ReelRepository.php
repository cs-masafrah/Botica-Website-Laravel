<?php
// Repositories/ReelRepository.php

namespace Webkul\Reel\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Reel\Contracts\Reel;
use Illuminate\Support\Facades\Event;

class ReelRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Reel\Contracts\Reel';
    }

    /**
     * Create reel with translations.
     */
    public function create(array $data)
    {
        // Extract translations using a more reliable method
        $translations = $this->extractTranslations($data);

        // Remove translation data from main data array
        $data = array_diff_key($data, $translations);

        // Create reel
        $reel = parent::create($data);

        // Create translations
        $this->createTranslations($reel, $translations);

        // Clear cache if needed
        $this->clearCache();

        return $reel;
    }

    /**
     * Update reel with translations.
     */
    public function update(array $data, $id, $attribute = "id")
    {
        $reel = $this->findOrFail($id);

        // Extract translations
        $translations = $this->extractTranslations($data);

        // Remove translation data from main data array
        $data = array_diff_key($data, $translations);

        // Update reel
        $reel = parent::update($data, $id);

        // Update or create translations
        $this->updateTranslations($reel, $translations);

        // Clear cache if needed
        $this->clearCache();

        return $reel;
    }

    /**
     * Extract translations from data array.
     */
    protected function extractTranslations(array &$data): array
    {
        $translations = [];

        // Get all available locales from Bagisto
        $locales = core()->getAllLocales()->pluck('code')->toArray();

        foreach ($data as $key => $value) {
            // Check if key matches any locale code
            if (in_array($key, $locales) && is_array($value)) {
                $translations[$key] = $value;
            }
        }

        return $translations;
    }

    /**
     * Create translations for a reel.
     */
    protected function createTranslations($reel, array $translations): void
    {
        foreach ($translations as $locale => $translationData) {
            // Validate required fields
            if (!isset($translationData['title'])) {
                throw new \InvalidArgumentException("Title is required for locale: {$locale}");
            }

            $reel->translations()->create([
                'locale'  => $locale,
                'title'   => $translationData['title'],
                'caption' => $translationData['caption'] ?? null,
            ]);
        }
    }

    /**
     * Update translations for a reel.
     */
    protected function updateTranslations($reel, array $translations): void
    {
        foreach ($translations as $locale => $translationData) {
            // Validate required fields
            if (!isset($translationData['title'])) {
                throw new \InvalidArgumentException("Title is required for locale: {$locale}");
            }

            $reel->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title'   => $translationData['title'],
                    'caption' => $translationData['caption'] ?? null,
                ]
            );
        }
    }

    /**
     * Update sort order for multiple reels.
     */
    public function updateSortOrder($id, $sortOrder)
    {
        return $this->model->where('id', $id)->update([
            'sort_order' => $sortOrder
        ]);
    }

    /**
     * Bulk update sort orders.
     */
    public function updateSortOrders(array $sortOrders): bool
    {
        foreach ($sortOrders as $item) {
            $this->updateSortOrder($item['id'], $item['sort_order']);
        }

        $this->clearCache();

        return true;
    }

    /**
     * Get reels with translations for a specific locale.
     */
    public function getWithLocale($locale = null, $paginate = null)
    {
        $locale = $locale ?: app()->getLocale();

        $query = $this->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])->whereHas('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        });

        if ($paginate) {
            return $query->paginate($paginate);
        }

        return $query->get();
    }

    /**
     * Get active reels with translations.
     */
    public function getActiveReels($locale = null, $limit = 10)
    {
        $locale = $locale ?: app()->getLocale();

        return $this->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->where('is_active', true)
            ->whereHas('translations', function ($q) use ($locale) {
                $q->where('locale', $locale);
            })
            ->orderBy('sort_order')
            ->limit($limit)
            ->get();
    }

    /**
     * Clear cache if you're using caching.
     */
    protected function clearCache(): void
    {
        if (method_exists($this, 'clear')) {
            $this->clear();
        }
    }

    public function getWithLikeStatus($customerId = null, $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        $customerId = $customerId ?: auth()->guard('customer')->id();

        $reels = $this->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])->whereHas('translations', function ($q) use ($locale) {
            $q->where('locale', $locale);
        })->get();

        if ($customerId) {
            $reels->each(function ($reel) use ($customerId) {
                $reel->setAttribute('is_liked', $reel->likes()->where('customer_id', $customerId)->exists());
            });
        }

        return $reels;
    }

    /**
     * Increment views count.
     */
    public function incrementViews($id): void
    {
        $this->model->where('id', $id)->increment('views_count');
    }

    /**
     * Toggle like status.
     */
    public function toggleLike($id, $customerId): bool
    {
        $reel = $this->findOrFail($id);

        $existingLike = $reel->likes()->where('customer_id', $customerId)->first();

        if ($existingLike) {
            $existingLike->delete();
            $reel->decrement('likes_count');
            return false; // unliked
        } else {
            $reel->likes()->create(['customer_id' => $customerId]);
            $reel->increment('likes_count');
            return true; // liked
        }
    }
}

<?php
// Traits/Translatable.php

namespace Webkul\Reel\Traits;

trait Translatable
{
    /**
     * Get translation for current locale.
     */
    public function getTranslation($locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $this->translations->where('locale', $locale)->first();
    }

    /**
     * Get translated attribute.
     */
    public function getTranslatedAttribute($attribute, $locale = null)
    {
        $translation = $this->getTranslation($locale);

        return $translation ? $translation->$attribute : null;
    }
}

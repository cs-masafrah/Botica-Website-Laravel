<?php
// Contracts/ReelLike.php

namespace Webkul\Reel\Contracts;

interface ReelLike
{
    /**
     * Get the reel that owns the like.
     */
    public function reel();

    /**
     * Get the customer that owns the like.
     */
    public function customer();
}

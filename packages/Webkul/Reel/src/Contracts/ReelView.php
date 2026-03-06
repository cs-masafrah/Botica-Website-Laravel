<?php
// Contracts/ReelView.php

namespace Webkul\Reel\Contracts;

interface ReelView
{
    /**
     * Get the reel that owns the view.
     */
    public function reel();

    /**
     * Get the customer that owns the view.
     */
    public function customer();
}

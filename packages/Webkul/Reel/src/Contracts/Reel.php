<?php

namespace Webkul\Reel\Contracts;

interface Reel
{
    public function translate($locale = null);
    public function getTitleAttribute();
    public function getCaptionAttribute();
}

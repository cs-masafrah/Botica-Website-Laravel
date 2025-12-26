<?php

namespace Webkul\Reel\Repositories;

use Webkul\Core\Eloquent\Repository;

class ReelRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Webkul\Reel\Contracts\Reel';
    }
}
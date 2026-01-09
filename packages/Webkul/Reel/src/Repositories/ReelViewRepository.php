<?php

namespace Webkul\Reel\Repositories;

use Webkul\Core\Eloquent\Repository;

class ReelViewRepository extends Repository
{
    public function model()
    {
        return 'Webkul\Reel\Contracts\ReelView';
    }
}

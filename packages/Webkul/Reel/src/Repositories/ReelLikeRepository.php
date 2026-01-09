<?php

namespace Webkul\Reel\Repositories;

use Webkul\Core\Eloquent\Repository;

class ReelLikeRepository extends Repository
{
    public function model()
    {
        return 'Webkul\Reel\Contracts\ReelLike';
    }
}

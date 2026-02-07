<?php

namespace Webkul\AbandonedCart\Repositories;

use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Event;

class AbandonedCartRuleRepository extends Repository
{
    public function model()
    {
        return 'Webkul\AbandonedCart\Models\AbandonedCartRule';
    }

    public function create(array $data)
    {
        Event::dispatch('abandonedcart.rule.create.before', $data);

        $rule = parent::create($data);

        Event::dispatch('abandonedcart.rule.create.after', $rule);

        return $rule;
    }

    public function update(array $data, $id, $attribute = "id")
    {
        $rule = $this->findOrFail($id);

        Event::dispatch('abandonedcart.rule.update.before', $id);

        parent::update($data, $id, $attribute);

        Event::dispatch('abandonedcart.rule.update.after', $rule);

        return $rule;
    }

    public function getActiveRules()
    {
        return $this->model->where('status', 'active')->get();
    }
}

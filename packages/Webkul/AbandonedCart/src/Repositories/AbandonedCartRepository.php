<?php

namespace Webkul\AbandonedCart\Repositories;

use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Facades\Event;

class AbandonedCartRepository extends Repository
{
    public function model()
    {
        return 'Webkul\AbandonedCart\Models\AbandonedCart';
    }

    public function create(array $data)
    {
        Event::dispatch('abandonedcart.cart.create.before', $data);

        $abandonedCart = parent::create($data);

        Event::dispatch('abandonedcart.cart.create.after', $abandonedCart);

        return $abandonedCart;
    }

    public function update(array $data, $id, $attribute = "id")
    {
        $abandonedCart = $this->findOrFail($id);

        Event::dispatch('abandonedcart.cart.update.before', $id);

        parent::update($data, $id, $attribute);

        Event::dispatch('abandonedcart.cart.update.after', $abandonedCart);

        return $abandonedCart;
    }

    public function getCartsForReminder()
    {
        $config = core()->getConfigData('sales.abandoned_cart');

        if (empty($config['enabled']) || !$config['enabled']) {
            return collect();
        }

        $abandonedAfter = $config['abandoned_after'] ?? 60;
        $maxReminders = $config['max_reminders'] ?? 3;

        return $this->model
            ->where('is_converted', false)
            ->where('reminder_count', '<', $maxReminders)
            ->whereHas('cart', function ($query) {
                $query->where('is_active', 1);
            })
            ->where(function ($query) use ($abandonedAfter) {
                $query->whereNull('last_reminder_sent_at')
                    ->orWhere('last_reminder_sent_at', '<', now()->subMinutes($abandonedAfter));
            })
            ->with(['customer', 'cart.items.product', 'channel'])
            ->get();
    }
}

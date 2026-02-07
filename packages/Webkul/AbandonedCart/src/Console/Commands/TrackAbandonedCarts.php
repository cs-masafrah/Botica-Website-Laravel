<?php

namespace Webkul\AbandonedCart\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\AbandonedCart\Repositories\AbandonedCartRepository;
use Carbon\Carbon;

class TrackAbandonedCarts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abandoned-cart:track';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Track abandoned carts';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected CartRepository $cartRepository,
        protected AbandonedCartRepository $abandonedCartRepository
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting abandoned cart tracking...');

        $config = core()->getConfigData('sales.abandoned_cart');

        if (empty($config['enabled']) || !$config['enabled']) {
            $this->info('Abandoned cart tracking is disabled.');
            return 0;
        }

        $abandonedAfter = $config['abandoned_after'] ?? 60;
        $thresholdTime = Carbon::now()->subMinutes($abandonedAfter);

        $carts = $this->cartRepository
            ->where('is_active', 1)
            ->whereNotNull('customer_id')
            ->where('updated_at', '<', $thresholdTime)
            ->whereDoesntHave('abandonedCart')
            ->with(['customer', 'items.product'])
            ->get();

        $trackedCount = 0;

        foreach ($carts as $cart) {
            $this->createAbandonedCartRecord($cart);
            $trackedCount++;
        }

        $this->info("Successfully tracked {$trackedCount} abandoned carts.");

        return 0;
    }

    /**
     * Create abandoned cart record.
     *
     * @param  mixed  $cart
     * @return void
     */
    private function createAbandonedCartRecord($cart)
    {
        $cartItems = $cart->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'sku' => $item->sku,
                'name' => $item->name,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'total' => $item->total,
                'product' => [
                    'url_key' => $item->product->url_key ?? '',
                    'images' => $item->product->images ?? []
                ]
            ];
        })->toArray();

        $this->abandonedCartRepository->create([
            'cart_id' => $cart->id,
            'customer_id' => $cart->customer_id,
            'customer_email' => $cart->customer_email,
            'customer_first_name' => $cart->customer->first_name ?? '',
            'customer_last_name' => $cart->customer->last_name ?? '',
            'cart_items' => $cartItems,
            'cart_total' => $cart->grand_total,
            'items_count' => $cart->items->count(),
            'abandoned_at' => Carbon::now(),
            'channel_id' => $cart->channel_id,
            'locale' => $cart->locale,
        ]);
    }
}

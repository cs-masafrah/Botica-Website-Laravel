<?php

namespace Webkul\AbandonedCart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
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
        Log::info('=== STARTING ABANDONED CART TRACKING PROCESS ===');
        $this->info('=== STARTING ABANDONED CART TRACKING PROCESS ===');

        try {
            // Step 1: Get configuration
            Log::info('STEP 1: Retrieving abandoned cart configuration');
            $this->info('STEP 1: Retrieving configuration...');

            $config = $this->getConfig();

            Log::info('Configuration retrieved', [
                'config_exists' => !empty($config),
                'enabled' => $config['enabled'] ?? false,
                'abandoned_after_minutes' => $config['abandoned_after'] ?? 60,
                'raw_config' => $config
            ]);

            if (empty($config['enabled']) || !$config['enabled']) {
                Log::info('Abandoned cart tracking is disabled. Process terminated.');
                $this->info('Abandoned cart tracking is disabled.');
                return 0;
            }

            // Step 2: Calculate threshold time
            $abandonedAfter = $config['abandoned_after'] ?? 60;
            $thresholdTime = Carbon::now()->subMinutes($abandonedAfter);

            Log::info('STEP 2: Calculating abandonment threshold', [
                'abandoned_after_minutes' => $abandonedAfter,
                'current_time' => Carbon::now()->toDateTimeString(),
                'threshold_time' => $thresholdTime->toDateTimeString(),
                'minutes_back' => $abandonedAfter
            ]);

            $this->info("Looking for carts inactive for {$abandonedAfter} minutes (before {$thresholdTime})");

            // Step 3: Query for abandoned carts
            Log::info('STEP 3: Querying for potentially abandoned carts');
            $this->info('STEP 3: Querying database for abandoned carts...');

            $carts = $this->cartRepository
                ->where('is_active', 1)
                ->whereNotNull('customer_id')
                ->where('updated_at', '<', $thresholdTime)
                ->whereDoesntHave('abandonedCart')
                ->with(['customer', 'items.product'])
                ->get();

            Log::info('Carts query executed', [
                'sql_conditions' => [
                    'is_active' => 1,
                    'customer_id_not_null' => true,
                    'updated_before' => $thresholdTime->toDateTimeString(),
                    'no_existing_abandoned_cart_record' => true
                ],
                'carts_found' => $carts->count(),
                'carts_details' => $carts->map(function ($cart) {
                    return [
                        'cart_id' => $cart->id,
                        'customer_id' => $cart->customer_id,
                        'customer_email' => $cart->customer_email,
                        'updated_at' => $cart->updated_at ? $cart->updated_at->toDateTimeString() : null,
                        'grand_total' => $cart->grand_total,
                        'items_count' => $cart->items->count(),
                        'has_customer' => !is_null($cart->customer),
                        'customer_name' => $cart->customer ? "{$cart->customer->first_name} {$cart->customer->last_name}" : 'N/A'
                    ];
                })->toArray()
            ]);

            $this->info("Found {$carts->count()} potential abandoned cart(s)");

            if ($carts->isEmpty()) {
                Log::info('No abandoned carts found matching criteria. Process completed.');
                $this->info('No abandoned carts found.');
                return 0;
            }

            // Step 4: Process each cart
            Log::info('STEP 4: Processing individual carts');
            $trackedCount = 0;
            $errorCount = 0;
            $cartIndex = 0;

            foreach ($carts as $cart) {
                $cartIndex++;
                Log::info("Processing cart {$cartIndex}/{$carts->count()}: #{$cart->id}");
                $this->info("  Processing cart {$cartIndex}/{$carts->count()}: #{$cart->id}");

                try {
                    $result = $this->createAbandonedCartRecord($cart);

                    if ($result) {
                        $trackedCount++;
                        Log::info("✓ Successfully created abandoned cart record for cart #{$cart->id}");
                        $this->info("  ✓ Tracked cart #{$cart->id}");
                    } else {
                        $errorCount++;
                        Log::warning("Failed to create abandoned cart record for cart #{$cart->id}");
                        $this->warn("  ✗ Failed to track cart #{$cart->id}");
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    Log::error("Exception while processing cart #{$cart->id}", [
                        'error_message' => $e->getMessage(),
                        'error_file' => $e->getFile(),
                        'error_line' => $e->getLine(),
                        'cart_id' => $cart->id,
                        'customer_email' => $cart->customer_email
                    ]);
                    $this->error("  ✗ Error processing cart #{$cart->id}: " . $e->getMessage());
                }
            }

            // Step 5: Final summary
            Log::info('=== ABANDONED CART TRACKING COMPLETED ===', [
                'total_carts_found' => $carts->count(),
                'successfully_tracked' => $trackedCount,
                'failed_attempts' => $errorCount,
                'success_rate' => $carts->count() > 0 ? round(($trackedCount / $carts->count()) * 100, 2) . '%' : 'N/A',
                'process_completed_at' => Carbon::now()->toDateTimeString()
            ]);

            $this->info("=== PROCESS COMPLETED ===");
            $this->info("Successfully tracked: {$trackedCount} cart(s)");
            $this->info("Failed: {$errorCount} cart(s)");
            $this->info("Total processed: {$carts->count()} cart(s)");

            return 0;
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in abandoned cart tracking process', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString(),
                'process_failed_at' => Carbon::now()->toDateTimeString()
            ]);

            $this->error("CRITICAL ERROR: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());

            return 1;
        }
    }

    /**
     * Get abandoned cart configuration.
     *
     * @return array
     */
    private function getConfig()
    {
        Log::info('Starting configuration retrieval');

        $config = [
            'enabled' => false,
            'abandoned_after' => 60,
            'max_reminders' => 3,
        ];

        try {
            // Check if core() helper exists
            if (function_exists('core')) {
                Log::info('core() helper function found. Fetching configuration values...');

                // Get individual config values
                $enabled = core()->getConfigData('sales.abandoned_cart.general.status');
                $abandonedAfter = core()->getConfigData('sales.abandoned_cart.general.abandoned_after');
                $maxReminders = core()->getConfigData('sales.abandoned_cart.general.max_reminders');

                $config['enabled'] = (bool) ($enabled ?? false);
                $config['abandoned_after'] = (int) ($abandonedAfter ?? 60);
                $config['max_reminders'] = (int) ($maxReminders ?? 3);

                Log::info('Configuration values retrieved', [
                    'enabled_raw' => $enabled,
                    'enabled_processed' => $config['enabled'],
                    'abandoned_after_raw' => $abandonedAfter,
                    'abandoned_after_processed' => $config['abandoned_after'],
                    'max_reminders_raw' => $maxReminders,
                    'max_reminders_processed' => $config['max_reminders']
                ]);

                $this->info("Config retrieved - Enabled: " . ($config['enabled'] ? 'Yes' : 'No'));
            } else {
                Log::warning('core() helper function not found. Using default configuration.');
                $this->warn('core() helper not found! Using default configuration.');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching abandoned cart configuration', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString()
            ]);
            $this->error('Config error: ' . $e->getMessage());
        }

        return $config;
    }

    /**
     * Create abandoned cart record.
     *
     * @param  mixed  $cart
     * @return bool
     */
    private function createAbandonedCartRecord($cart)
    {
        Log::info("Creating abandoned cart record for cart #{$cart->id}", [
            'cart_id' => $cart->id,
            'customer_id' => $cart->customer_id,
            'customer_email' => $cart->customer_email,
            'cart_total' => $cart->grand_total,
            'items_count' => $cart->items->count(),
            'has_customer' => !is_null($cart->customer),
            'customer_name' => $cart->customer ? "{$cart->customer->first_name} {$cart->customer->last_name}" : 'N/A'
        ]);

        try {
            // Prepare cart items
            $cartItems = [];
            $itemIndex = 0;

            foreach ($cart->items as $item) {
                $itemIndex++;
                Log::info("Processing item {$itemIndex} for cart #{$cart->id}", [
                    'item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'sku' => $item->sku,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                    'has_product' => !is_null($item->product)
                ]);

                $cartItems[] = [
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
            }

            Log::info("Prepared cart items data for cart #{$cart->id}", [
                'total_items' => count($cartItems),
                'sample_item' => !empty($cartItems) ? $cartItems[0] : 'No items'
            ]);

            // Prepare record data
            $recordData = [
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
            ];

            Log::info("Attempting to create abandoned cart record in database", [
                'cart_id' => $cart->id,
                'data_structure' => [
                    'has_cart_items' => !empty($recordData['cart_items']),
                    'cart_items_count' => count($recordData['cart_items']),
                    'cart_total' => $recordData['cart_total'],
                    'customer_email' => $recordData['customer_email'],
                    'timestamp' => Carbon::now()->toDateTimeString()
                ]
            ]);

            // Create the record
            $result = $this->abandonedCartRepository->create($recordData);

            if ($result) {
                Log::info("✓ Successfully created abandoned cart record", [
                    'abandoned_cart_id' => $result->id,
                    'cart_id' => $cart->id,
                    'created_at' => $result->created_at ? $result->created_at->toDateTimeString() : 'N/A'
                ]);

                // Log additional details about the created record
                Log::info("Abandoned cart record details", [
                    'record_id' => $result->id,
                    'original_cart_id' => $result->cart_id,
                    'customer_email' => $result->customer_email,
                    'abandoned_at' => $result->abandoned_at ? $result->abandoned_at->toDateTimeString() : null,
                    'items_count' => $result->items_count,
                    'cart_total' => $result->cart_total
                ]);

                return true;
            } else {
                Log::error("Failed to create abandoned cart record - Repository returned false/null", [
                    'cart_id' => $cart->id,
                    'customer_email' => $cart->customer_email
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Exception while creating abandoned cart record for cart #{$cart->id}", [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'cart_id' => $cart->id,
                'customer_email' => $cart->customer_email,
                'stack_trace' => $e->getTraceAsString()
            ]);

            throw $e; // Re-throw to be caught by the main handler
        }
    }
}

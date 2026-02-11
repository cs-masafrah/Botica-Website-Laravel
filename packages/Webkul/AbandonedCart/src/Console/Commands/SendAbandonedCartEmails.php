<?php

namespace Webkul\AbandonedCart\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Webkul\AbandonedCart\Mail\AbandonedCartEmail;
use Webkul\AbandonedCart\Repositories\AbandonedCartRepository;
use Webkul\AbandonedCart\Repositories\AbandonedCartRuleRepository;

class SendAbandonedCartEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'abandoned-cart:send-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send abandoned cart reminder emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        protected AbandonedCartRepository $abandonedCartRepository,
        protected AbandonedCartRuleRepository $ruleRepository
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
        $this->info('=== STARTING ABANDONED CART EMAIL PROCESS ===');
        Log::info('=== STARTING ABANDONED CART EMAIL PROCESS ===');

        try {
            // Get abandoned cart configuration
            $this->info('STEP 1: Retrieving configuration...');
            Log::info('Retrieving abandoned cart configuration');

            $config = $this->getConfig();

            Log::info('Configuration retrieved', [
                'enabled' => $config['enabled'],
                'abandoned_after' => $config['abandoned_after'],
                'max_reminders' => $config['max_reminders']
            ]);

            $this->info('Config status: ' . ($config['enabled'] ? 'ENABLED' : 'DISABLED'));

            if (!$config['enabled']) {
                $this->info('Abandoned cart emails are disabled. Exiting.');
                Log::info('Abandoned cart emails are disabled. Process terminated.');
                return 0;
            }

            // Get active rules
            $this->info('STEP 2: Fetching active rules...');
            Log::info('Fetching active abandoned cart rules from repository');

            $rules = $this->ruleRepository->getActiveRules();

            Log::info('Active rules fetched', [
                'rules_count' => $rules->count(),
                'rules_details' => $rules->map(function ($rule) {
                    return [
                        'id' => $rule->id,
                        'name' => $rule->name,
                        'status' => $rule->status,
                        'abandoned_after_minutes' => $rule->abandoned_after_minutes,
                        'send_after_minutes' => $rule->send_after_minutes,
                        'max_reminders' => $rule->max_reminders,
                        'include_coupon' => $rule->include_coupon,
                        'channels_count' => is_array($rule->channel_ids) ? count($rule->channel_ids) : 0,
                        'customer_groups_count' => is_array($rule->customer_group_ids) ? count($rule->customer_group_ids) : 0,
                    ];
                })->toArray()
            ]);

            $this->info("Found {$rules->count()} active rule(s).");

            if ($rules->isEmpty()) {
                $this->info('No active rules found. Exiting.');
                Log::info('No active abandoned cart rules found. Process terminated.');
                return 0;
            }

            $sentCount = 0;
            $ruleIndex = 0;

            foreach ($rules as $rule) {
                $ruleIndex++;
                $this->info("RULE {$ruleIndex}/{$rules->count()}: Processing rule #{$rule->id} - {$rule->name}");

                Log::info("Processing rule #{$rule->id}: {$rule->name}", [
                    'rule_details' => [
                        'id' => $rule->id,
                        'name' => $rule->name,
                        'abandoned_after_minutes' => $rule->abandoned_after_minutes,
                        'send_after_minutes' => $rule->send_after_minutes,
                        'max_reminders' => $rule->max_reminders,
                        'include_coupon' => $rule->include_coupon,
                    ]
                ]);

                // Get carts eligible for reminder
                $this->info("Fetching eligible carts for rule #{$rule->id}...");
                Log::info("Querying eligible carts for rule #{$rule->id}");

                $abandonedCarts = $this->abandonedCartRepository
                    ->where('is_converted', false)
                    ->where('reminder_count', '<', $rule->max_reminders)
                    ->where(function ($query) use ($rule) {
                        $query->whereNull('last_reminder_sent_at')
                            ->orWhere(
                                'last_reminder_sent_at',
                                '<',
                                Carbon::now()->subMinutes($rule->send_after_minutes)
                            );
                    })
                    ->with(['customer', 'cart'])
                    ->get();

                Log::info("Query executed for rule #{$rule->id}", [
                    'eligible_carts_count' => $abandonedCarts->count(),
                    'carts_details' => $abandonedCarts->map(function ($cart) {
                        return [
                            'id' => $cart->id,
                            'customer_email' => $cart->customer_email,
                            'channel_id' => $cart->channel_id,
                            'abandoned_at' => $cart->abandoned_at ? $cart->abandoned_at->toDateTimeString() : null,
                            'last_reminder_sent_at' => $cart->last_reminder_sent_at ? $cart->last_reminder_sent_at->toDateTimeString() : null,
                            'reminder_count' => $cart->reminder_count,
                            'is_converted' => $cart->is_converted,
                            'has_customer' => !is_null($cart->customer),
                            'customer_group_id' => $cart->customer ? $cart->customer->customer_group_id : null,
                        ];
                    })->toArray()
                ]);

                $this->info("Found {$abandonedCarts->count()} eligible cart(s) for rule #{$rule->id}");

                $cartIndex = 0;
                $ruleSentCount = 0;

                Log::info("Checking eligibility for cart #{$abandonedCarts->count()}");
                foreach ($abandonedCarts as $cart) {
                    $cartIndex++;
                    $this->info("  Cart {$cartIndex}/{$abandonedCarts->count()}: Checking cart #{$cart->id}");

                    Log::info("Checking eligibility for cart #{$cart->id}", [
                        'cart_details' => [
                            'id' => $cart->id,
                            'customer_email' => $cart->customer_email,
                            'channel_id' => $cart->channel_id,
                            'abandoned_at' => $cart->abandoned_at ? $cart->abandoned_at->toDateTimeString() : null,
                            'last_reminder_sent_at' => $cart->last_reminder_sent_at ? $cart->last_reminder_sent_at->toDateTimeString() : null,
                            'reminder_count' => $cart->reminder_count,
                        ]
                    ]);

                    if ($this->shouldSendForCart($cart, $rule)) {
                        $this->info("  ✓ Cart #{$cart->id} is eligible. Sending email...");
                        Log::info("Cart #{$cart->id} passed all eligibility checks. Attempting to send email.");

                        $emailSent = $this->sendEmail($cart, $rule);

                        if ($emailSent) {
                            $sentCount++;
                            $ruleSentCount++;

                            // Update cart after successful email
                            $cart->update([
                                'last_reminder_sent_at' => Carbon::now(),
                                'reminder_count' => $cart->reminder_count + 1
                            ]);

                            Log::info("Cart #{$cart->id} updated after email sent", [
                                'new_reminder_count' => $cart->reminder_count + 1,
                                'last_reminder_sent_at' => Carbon::now()->toDateTimeString()
                            ]);

                            $this->info("  ✓ Email sent to: {$cart->customer_email}");
                        }
                    } else {
                        Log::info("Cart #{$cart->id} is not eligible for email based on rule checks.");
                    }
                }

                Log::info("Completed processing for rule #{$rule->id}", [
                    'rule_name' => $rule->name,
                    'carts_processed' => $abandonedCarts->count(),
                    'emails_sent' => $ruleSentCount
                ]);

                $this->info("Rule #{$rule->id} completed: {$ruleSentCount} email(s) sent");
            }

            // Final summary
            $this->info("=== PROCESS COMPLETED ===");
            $this->info("Total emails sent: {$sentCount}");

            Log::info('=== ABANDONED CART EMAIL PROCESS COMPLETED ===', [
                'total_rules_processed' => $rules->count(),
                'total_emails_sent' => $sentCount,
                'process_time' => Carbon::now()->toDateTimeString()
            ]);

            return 0;
        } catch (\Exception $e) {
            Log::error('CRITICAL ERROR in abandoned cart email process', [
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_trace' => $e->getTraceAsString()
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
     * Check if email should be sent for cart.
     *
     * @param  mixed  $cart
     * @param  mixed  $rule
     * @return bool
     */
    private function shouldSendForCart($cart, $rule)
    {
        Log::info("Starting eligibility checks for cart #{$cart->id}", [
            'cart_id' => $cart->id,
            'rule_id' => $rule->id
        ]);

        // Check if cart was abandoned long enough
        $abandonedThreshold = Carbon::now()->subMinutes($rule->abandoned_after_minutes);
        $isAbandonedLongEnough = $cart->abandoned_at <= $abandonedThreshold;

        Log::info("Check 1: Abandoned time check", [
            'abandoned_at' => $cart->abandoned_at ? $cart->abandoned_at->toDateTimeString() : null,
            'threshold' => $abandonedThreshold->toDateTimeString(),
            'minutes_required' => $rule->abandoned_after_minutes,
            'result' => $isAbandonedLongEnough ? 'PASS' : 'FAIL'
        ]);

        if (!$isAbandonedLongEnough) {
            Log::info("Cart #{$cart->id} not abandoned long enough. Requires {$rule->abandoned_after_minutes} minutes.");
            return false;
        }

        // Check if enough time has passed since last reminder
        $sendAfterThreshold = Carbon::now()->subMinutes($rule->send_after_minutes);
        $isSendAfterValid = true;

        if ($cart->last_reminder_sent_at) {
            $isSendAfterValid = $cart->last_reminder_sent_at <= $sendAfterThreshold;

            Log::info("Check 2: Time since last reminder", [
                'last_reminder_sent_at' => $cart->last_reminder_sent_at->toDateTimeString(),
                'threshold' => $sendAfterThreshold->toDateTimeString(),
                'minutes_required' => $rule->send_after_minutes,
                'result' => $isSendAfterValid ? 'PASS' : 'FAIL'
            ]);

            if (!$isSendAfterValid) {
                Log::info("Cart #{$cart->id} had reminder too recently. Requires {$rule->send_after_minutes} minutes between reminders.");
                return false;
            }
        } else {
            Log::info("Check 2: Time since last reminder - No previous reminder sent (PASS)");
        }

        // Check channel restrictions
        $channelCheck = true;
        if (!empty($rule->channel_ids) && is_array($rule->channel_ids)) {
            $channelCheck = in_array($cart->channel_id, $rule->channel_ids);

            Log::info("Check 3: Channel restrictions", [
                'cart_channel_id' => $cart->channel_id,
                'allowed_channels' => $rule->channel_ids,
                'result' => $channelCheck ? 'PASS' : 'FAIL'
            ]);

            if (!$channelCheck) {
                Log::info("Cart #{$cart->id} doesn't match channel restrictions.");
                return false;
            }
        } else {
            Log::info("Check 3: Channel restrictions - No restrictions (PASS)");
        }

        // Check customer group restrictions
        $customerGroupCheck = true;
        if (!empty($rule->customer_group_ids) && is_array($rule->customer_group_ids) && $cart->customer) {
            $customerGroupId = $cart->customer->customer_group_id;
            $customerGroupCheck = in_array($customerGroupId, $rule->customer_group_ids);

            Log::info("Check 4: Customer group restrictions", [
                'cart_customer_group_id' => $customerGroupId,
                'allowed_customer_groups' => $rule->customer_group_ids,
                'has_customer' => !is_null($cart->customer),
                'result' => $customerGroupCheck ? 'PASS' : 'FAIL'
            ]);

            if (!$customerGroupCheck) {
                Log::info("Cart #{$cart->id} doesn't match customer group restrictions.");
                return false;
            }
        } else {
            Log::info("Check 4: Customer group restrictions", [
                'has_customer' => !is_null($cart->customer),
                'restrictions_empty' => empty($rule->customer_group_ids),
                'result' => 'PASS'
            ]);
        }

        Log::info("Cart #{$cart->id} passed all eligibility checks for rule #{$rule->id}");
        return true;
    }

    /**
     * Send email for abandoned cart.
     *
     * @param  mixed  $cart
     * @param  mixed  $rule
     * @return bool
     */
    private function sendEmail($cart, $rule)
    {
        try {
            // Check if email should be sent
            if (empty($cart->customer_email)) {
                Log::warning("Cannot send email for cart #{$cart->id}: No email address found");
                $this->warn("No email address for cart {$cart->id}");
                return false;
            }

            $this->info("Preparing to send email to: {$cart->customer_email}");
            Log::info("Starting email send process", [
                'cart_id' => $cart->id,
                'customer_email' => $cart->customer_email,
                'rule_id' => $rule->id,
                'rule_name' => $rule->name
            ]);

            // Send email
            Mail::queue(new AbandonedCartEmail($cart, $rule));

            Log::info('Abandoned cart email successfully queued', [
                'cart_id' => $cart->id,
                'customer_email' => $cart->customer_email,
                'rule_id' => $rule->id,
                'rule_name' => $rule->name,
                'timestamp' => Carbon::now()->toDateTimeString()
            ]);

            $this->info("✓ Email queued for: {$cart->customer_email}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send abandoned cart email', [
                'cart_id' => $cart->id,
                'customer_email' => $cart->customer_email,
                'rule_id' => $rule->id,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ]);

            $this->error("✗ Failed to send email to: {$cart->customer_email} - " . $e->getMessage());
            return false;
        }
    }
}

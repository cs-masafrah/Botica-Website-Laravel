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
        $this->info('Starting abandoned cart email sending...');

        // Get abandoned cart configuration
        $config = $this->getConfig();

        $this->info('Config retrieved: ' . ($config['enabled'] ? 'Enabled' : 'Disabled'));

        if (!$config['enabled']) {
            $this->info('Abandoned cart emails are disabled.');
            return 0;
        }

        $rules = $this->ruleRepository->getActiveRules();

        if ($rules->isEmpty()) {
            $this->info('No active rules found.');
            return 0;
        }

        $this->info('Found ' . $rules->count() . ' active rules.');

        $sentCount = 0;

        foreach ($rules as $rule) {
            $this->info("Processing rule: {$rule->name}");

            // Get carts eligible for reminder
            $abandonedCarts = $this->abandonedCartRepository
                ->where('is_converted', false)
                ->where('reminder_count', '<', $rule->max_reminders)
                ->where(function ($query) use ($rule) {
                    $query->whereNull('last_reminder_sent_at')
                        ->orWhere(
                            'last_reminder_sent_at',
                            '<',
                            Carbon::now()->subMinutes($rule->send_after)
                        );
                })
                ->with(['customer', 'cart'])
                ->get();

            $this->info("Found {$abandonedCarts->count()} eligible carts for rule: {$rule->name}");

            foreach ($abandonedCarts as $cart) {
                if ($this->shouldSendForCart($cart, $rule)) {
                    $this->sendEmail($cart, $rule);
                    $sentCount++;

                    $cart->update([
                        'last_reminder_sent_at' => Carbon::now(),
                        'reminder_count' => $cart->reminder_count + 1
                    ]);

                    $this->info("Email sent to: {$cart->customer_email}");
                }
            }
        }

        $this->info("Sent {$sentCount} abandoned cart emails.");
        return 0;
    }

    /**
     * Get abandoned cart configuration.
     *
     * @return array
     */
    private function getConfig()
    {
        $config = [
            'enabled' => false,
            'abandoned_after' => 60,
            'max_reminders' => 3,
        ];

        try {
            // Check if core() helper exists
            if (function_exists('core')) {
                // Get individual config values
                $enabled = core()->getConfigData('sales.abandoned_cart.general.status');
                $abandonedAfter = core()->getConfigData('sales.abandoned_cart.general.abandoned_after');
                $maxReminders = core()->getConfigData('sales.abandoned_cart.general.max_reminders');

                $config['enabled'] = (bool) ($enabled ?? false);
                $config['abandoned_after'] = (int) ($abandonedAfter ?? 60);
                $config['max_reminders'] = (int) ($maxReminders ?? 3);

                // Debug output
                $this->info("Config values:");
                $this->info("- Enabled: " . ($config['enabled'] ? 'Yes' : 'No'));
                $this->info("- Abandoned After: {$config['abandoned_after']} minutes");
                $this->info("- Max Reminders: {$config['max_reminders']}");
            } else {
                $this->error('core() helper not found!');
            }
        } catch (\Exception $e) {
            Log::error('Error fetching abandoned cart config: ' . $e->getMessage());
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
        // Check if cart was abandoned long enough
        if ($cart->abandoned_at > Carbon::now()->subMinutes($rule->abandoned_after)) {
            $this->info("Cart {$cart->id} not abandoned long enough (needs {$rule->abandoned_after} minutes)");
            return false;
        }

        // Check if enough time has passed since last reminder
        if ($cart->last_reminder_sent_at &&
            $cart->last_reminder_sent_at > Carbon::now()->subMinutes($rule->send_after)) {
            $this->info("Cart {$cart->id} had reminder too recently");
            return false;
        }

        // Check channel restrictions
        if (!empty($rule->channel_ids) && !in_array($cart->channel_id, $rule->channel_ids)) {
            $this->info("Cart {$cart->id} doesn't match channel restrictions");
            return false;
        }

        // Check customer group restrictions
        if (!empty($rule->customer_group_ids) && $cart->customer) {
            $customerGroupId = $cart->customer->customer_group_id;
            if (!in_array($customerGroupId, $rule->customer_group_ids)) {
                $this->info("Cart {$cart->id} doesn't match customer group restrictions");
                return false;
            }
        }

        $this->info("Cart {$cart->id} is eligible for reminder");
        return true;
    }

    /**
     * Send email for abandoned cart.
     *
     * @param  mixed  $cart
     * @param  mixed  $rule
     * @return void
     */
    private function sendEmail($cart, $rule)
    {
        try {
            // Check if email should be sent
            if (empty($cart->customer_email)) {
                $this->warn("No email address for cart {$cart->id}");
                return;
            }

            $this->info("Sending email to: {$cart->customer_email}");

            // Send email
            Mail::queue(new AbandonedCartEmail($cart, $rule));

            Log::info('Abandoned cart email sent', [
                'cart_id' => $cart->id,
                'customer_email' => $cart->customer_email,
                'rule_id' => $rule->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send abandoned cart email: ' . $e->getMessage());
            $this->error("Failed to send email to: {$cart->customer_email} - " . $e->getMessage());
        }
    }
}

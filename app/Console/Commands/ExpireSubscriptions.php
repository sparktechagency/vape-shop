<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionWillExpireSoonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';
    protected $description = 'Finds expiring subscriptions, sends reminders, and marks expired ones.';

    public function handle()
    {
        $this->info('Starting subscription status check...');

        $this->sendExpirationReminders();
        $this->processExpiredSubscriptions();

        $this->info('Subscription status check completed.');
        return 0;
    }

    protected function sendExpirationReminders()
    {
        $this->line('Checking for subscriptions expiring in 3 days...');

        $subscriptionsWillExpireSoon = Subscription::with(['subscribable' => function ($morphTo) {
            $morphTo->morphWith([
                Branch::class => ['owner'],
            ]);
        }])
            ->where('invoice_status', 'paid')
            ->where('ends_at', '<=', now()->addDays(3)->startOfDay())
            ->whereNull('reminder_sent_at')
            ->get();

        if ($subscriptionsWillExpireSoon->isEmpty()) {
            $this->info('No subscriptions found that require a reminder.');
            return;
        }

        foreach ($subscriptionsWillExpireSoon as $subscription) {
            $user = $subscription->user;
            if ($user) {
                try {
                    $user->notify(new SubscriptionWillExpireSoonNotification($subscription));
                    $subscription->update(['reminder_sent_at' => now()]);
                    Log::info("Sent expiration reminder for subscription ID: {$subscription->id}");
                } catch (\Exception $e) {
                    Log::error("Failed to send reminder for subscription ID {$subscription->id}: " . $e->getMessage());
                }
            }
        }
        $this->info("Sent {$subscriptionsWillExpireSoon->count()} expiration reminders.");
    }

    protected function processExpiredSubscriptions()
    {
        $this->line('Checking for expired subscriptions...');


        $expiredSubscriptions = Subscription::with(['subscribable' => function ($morphTo) {
            $morphTo->morphWith([
                Branch::class => ['owner'],
            ]);
        }])
            ->where('invoice_status', 'paid')
            ->where('ends_at', '<', now())
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found to process.');
            return;
        }

        foreach ($expiredSubscriptions as $subscription) {
            $user = $subscription->user;
            if ($user) {
                try {

                    $subscription->update(['invoice_status' => 'expired']);
                    if ($subscription->subscribable_type === Branch::class) {
                        $subscription->subscribable->update(['is_active' => false]);
                    }

                    
                    $user->notify(new SubscriptionExpiredNotification($subscription));

                    Log::info("Subscription ID {$subscription->id} has been marked as expired and user notified.");
                } catch (\Exception $e) {
                    Log::error("Failed to process expiration for subscription ID {$subscription->id}: " . $e->getMessage());
                }
            }
        }
        $this->info("Processed and expired {$expiredSubscriptions->count()} subscriptions.");
    }
}

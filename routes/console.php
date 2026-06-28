<?php

use App\Jobs\CollectRentJob;
use App\Jobs\SendRentReminderJob;
use App\Models\{Lease, Payment, Property};
use Illuminate\Support\Facades\{Artisan, Schedule};

Artisan::command('properties:sync-status', function () {
    $count = 0;
    Property::query()->chunkById(100, function ($properties) use (&$count) {
        foreach ($properties as $property) {
            $property->syncStatusFromLeases();
            $count++;
        }
    });
    $this->info("Synced properties.status for {$count} properties.");
})->purpose('Align properties.status with active lease count (active / vacant)');

// ─────────────────────────────────────────────────────────────
// RENT COLLECTION — runs daily at 00:01 UTC
// Dispatches CollectRentJob for every active lease due today
// ─────────────────────────────────────────────────────────────
Schedule::call(function () {
    $today = now()->day;

    Lease::where('status', 'active')
        ->where('due_day', $today)
        ->whereHas('property') // ensure property still exists
        ->with('property')
        ->chunk(100, function ($leases) {
            foreach ($leases as $lease) {
                CollectRentJob::dispatch($lease);
            }
        });

})->dailyAt('00:01')->name('collect-rent')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────
// ARREARS REMINDERS — runs daily at 09:00 UTC
// Sends escalating reminders for overdue payments
// ─────────────────────────────────────────────────────────────
Schedule::call(function () {
    $overduePayments = Payment::where('status', 'pending')
        ->where('due_date', '<', now()->subDay())
        ->with('lease.tenant', 'lease.property.landlord')
        ->get();

    foreach ($overduePayments as $payment) {
        $daysOverdue = now()->diffInDays($payment->due_date);

        // Send on day 1, 5, and 10
        if (in_array($daysOverdue, [1, 5, 10])) {
            SendRentReminderJob::dispatch($payment, $daysOverdue);
        }

        // Mark as failed after day 10 (second retry already done)
        if ($daysOverdue > 10 && $payment->retry_count >= 2) {
            $payment->update(['status' => 'failed']);
        }
    }

})->dailyAt('09:00')->name('arrears-reminders')->withoutOverlapping();

// ─────────────────────────────────────────────────────────────
// TEMPLATE EMAILS — runs daily at 07:00 UTC
// Sends landlord-managed email templates (reminders, overdue,
// late fee, lease expiry) based on published EmailTemplate rules.
// Idempotent: duplicate-send prevention via email_sent_logs.
// ─────────────────────────────────────────────────────────────
Schedule::command('rent:send-emails')
    ->dailyAt('07:00')
    ->name('template-emails')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/rent-emails.log'));

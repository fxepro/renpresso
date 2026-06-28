<?php

namespace App\Console\Commands;

use App\Mail\EmailTemplateMail;
use App\Models\EmailSentLog;
use App\Models\EmailTemplate;
use App\Models\Lease;
use App\Models\LandlordEmailPreference;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendRentEmails extends Command
{
    protected $signature   = 'rent:send-emails {--dry-run : Print what would be sent without actually sending}';
    protected $description = 'Send scheduled tenant email notifications (reminders, overdue, etc.)';

    private bool $dryRun = false;
    private int  $sent   = 0;
    private int  $skipped = 0;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        $today = Carbon::today();

        if ($this->dryRun) {
            $this->warn('DRY RUN — no emails will be sent.');
        }

        $templates = EmailTemplate::published();

        if ($templates->isEmpty()) {
            $this->info('No published templates found.');
            return 0;
        }

        // Load all active leases with relations
        $leases = Lease::where('status', 'active')
            ->with([
                'property:id,name,address_line1,city,country_code,landlord_id',
                'property.landlord:id,first_name,last_name,email',
                'tenant:id,first_name,last_name,email',
            ])
            ->get();

        foreach ($templates as $template) {
            $this->processTemplate($template, $leases, $today);
        }

        // Lease expiry notices (separate — not payment-based)
        $expiryTemplates = $templates->where('trigger_event', 'lease_expiry_days');
        foreach ($expiryTemplates as $template) {
            $this->processLeaseExpiryTemplate($template, $leases, $today);
        }

        $this->info("Done. Sent: {$this->sent} · Skipped: {$this->skipped}");

        return 0;
    }

    private function processTemplate(EmailTemplate $template, $leases, Carbon $today): void
    {
        if ($template->trigger_event === 'lease_expiry_days') {
            return; // handled separately
        }

        match ($template->trigger_event) {
            'rent_due_in_days'  => $this->handleDueSoonTemplate($template, $leases, $today),
            'rent_overdue_days' => $this->handleOverdueTemplate($template, $leases, $today),
            'late_fee_applied'  => $this->handleLateFeeTemplate($template, $leases, $today),
            default             => null, // payment_success / payment_failed are event-driven, not scheduled
        };
    }

    /** Reminders: N days before due date (or on due day). */
    private function handleDueSoonTemplate(EmailTemplate $template, $leases, Carbon $today): void
    {
        $daysAhead  = (int) ($template->trigger_days ?? 0);
        $targetDate = $today->copy()->addDays($daysAhead);

        // Payments due on targetDate that haven't been collected
        $pendingPayments = Payment::query()
            ->whereDate('due_date', $targetDate)
            ->whereNotIn('status', ['success'])
            ->pluck('lease_id')
            ->toArray();

        foreach ($leases as $lease) {
            if (! in_array($lease->id, $pendingPayments)) {
                continue;
            }

            $payment = Payment::where('lease_id', $lease->id)
                ->whereDate('due_date', $targetDate)
                ->whereNotIn('status', ['success'])
                ->first();

            if (! $payment) {
                continue;
            }

            $this->attemptSend($template, $lease, $payment->due_date);
        }
    }

    /** Overdue: N days after due with no successful payment. */
    private function handleOverdueTemplate(EmailTemplate $template, $leases, Carbon $today): void
    {
        $daysLate   = (int) ($template->trigger_days ?? 3);
        $targetDate = $today->copy()->subDays($daysLate);

        $overduePayments = Payment::query()
            ->whereDate('due_date', $targetDate)
            ->whereIn('status', ['failed', 'pending'])
            ->pluck('lease_id')
            ->toArray();

        foreach ($leases as $lease) {
            if (! in_array($lease->id, $overduePayments)) {
                continue;
            }

            $payment = Payment::where('lease_id', $lease->id)
                ->whereDate('due_date', $targetDate)
                ->whereIn('status', ['failed', 'pending'])
                ->first();

            if (! $payment) {
                continue;
            }

            $this->attemptSend($template, $lease, $payment->due_date);
        }
    }

    /** Late fee notice: fires when today equals the lease's late fee day. */
    private function handleLateFeeTemplate(EmailTemplate $template, $leases, Carbon $today): void
    {
        foreach ($leases as $lease) {
            $lateFeeDay = $lease->lateFeeDayOfMonth();
            if ($today->day !== $lateFeeDay) {
                continue;
            }

            // Only send if this month's payment hasn't been collected
            $hasSuccessThisMonth = Payment::where('lease_id', $lease->id)
                ->whereYear('due_date', $today->year)
                ->whereMonth('due_date', $today->month)
                ->where('status', 'success')
                ->exists();

            if ($hasSuccessThisMonth) {
                continue;
            }

            $triggerDate = $today->copy()->startOfMonth()->addDays($lateFeeDay - 1);
            $this->attemptSend($template, $lease, $triggerDate->toDateString());
        }
    }

    private function processLeaseExpiryTemplate(EmailTemplate $template, $leases, Carbon $today): void
    {
        $daysAhead  = (int) ($template->trigger_days ?? 30);
        $targetDate = $today->copy()->addDays($daysAhead);

        foreach ($leases as $lease) {
            if (! $lease->end_date) {
                continue;
            }

            if (! $lease->end_date->isSameDay($targetDate)) {
                continue;
            }

            $this->attemptSend($template, $lease, $lease->end_date->toDateString());
        }
    }

    private function attemptSend(EmailTemplate $template, Lease $lease, $triggerDate): void
    {
        $tenant   = $lease->tenant;
        $landlord = $lease->property?->landlord;

        if (! $tenant || ! $landlord || ! $tenant->email) {
            $this->skipped++;
            return;
        }

        // Unique key prevents double-sends
        $triggerKey = "{$template->slug}:{$lease->id}:{$triggerDate}";

        if (EmailSentLog::where('trigger_key', $triggerKey)->exists()) {
            $this->skipped++;
            return;
        }

        // Check landlord preference
        $pref = LandlordEmailPreference::where('landlord_id', $landlord->id)
            ->where('email_template_id', $template->id)
            ->first();

        $isEnabled = $pref ? $pref->is_enabled : true;

        if (! $isEnabled) {
            $this->logSend($template, $lease, $tenant->email, '', $triggerDate, $triggerKey, 'skipped');
            $this->skipped++;
            return;
        }

        // Resolve subject and body (landlord override or platform default)
        $subject = $pref?->subject_override ?: $template->subject;
        $body    = $pref?->body_html_override ?: $template->body_html;

        // Build variables map
        $vars = $this->buildVars($lease, $triggerDate);

        $subject = $template->render($subject, $vars);
        $body    = $template->render($body, $vars);

        if ($this->dryRun) {
            $this->line("  → [{$template->slug}] To: {$tenant->email} | Subject: {$subject}");
            $this->sent++;
            return;
        }

        try {
            Mail::to($tenant->email)->send(new EmailTemplateMail(
                emailSubject: $subject,
                bodyHtml:     $body,
                tenantName:   "{$tenant->first_name} {$tenant->last_name}",
            ));

            $this->logSend($template, $lease, $tenant->email, $subject, $triggerDate, $triggerKey, 'sent');
            $this->sent++;

        } catch (\Throwable $e) {
            $this->logSend($template, $lease, $tenant->email, $subject, $triggerDate, $triggerKey, 'failed', $e->getMessage());
            $this->error("  ✗ Failed to send to {$tenant->email}: {$e->getMessage()}");
        }
    }

    private function buildVars(Lease $lease, $triggerDate): array
    {
        $tenant   = $lease->tenant;
        $landlord = $lease->property?->landlord;
        $property = $lease->property;
        $today    = Carbon::today();
        $dueDate  = Carbon::parse($triggerDate);

        return [
            'tenant_first_name'  => $tenant?->first_name ?? '',
            'tenant_name'        => trim(($tenant?->first_name ?? '') . ' ' . ($tenant?->last_name ?? '')),
            'landlord_first_name'=> $landlord?->first_name ?? '',
            'landlord_name'      => trim(($landlord?->first_name ?? '') . ' ' . ($landlord?->last_name ?? '')),
            'property_name'      => $property?->name ?? '',
            'property_address'   => trim(($property?->address_line1 ?? '') . ' ' . ($property?->city ?? '')),
            'rent_amount'        => number_format($lease->rent_minor_units / 100, 2),
            'currency_code'      => $lease->currency_code ?? '',
            'due_date'           => $dueDate->format('d F Y'),
            'due_day'            => Lease::ordinalDay((int) $lease->due_day),
            'days_until_due'     => max(0, $today->diffInDays($dueDate, false)),
            'days_overdue'       => max(0, $dueDate->diffInDays($today)),
            'late_fee_amount'    => number_format(($lease->late_fee_minor_units ?? 0) / 100, 2),
            'lease_end_date'     => $lease->end_date?->format('d F Y') ?? '—',
            'days_until_expiry'  => $lease->end_date ? $today->diffInDays($lease->end_date) : '—',
            'platform_name'      => config('app.name', 'Renpresso'),
        ];
    }

    private function logSend(
        EmailTemplate $template,
        Lease $lease,
        string $toEmail,
        string $subject,
        $triggerDate,
        string $triggerKey,
        string $status,
        ?string $error = null,
    ): void {
        EmailSentLog::create([
            'email_template_id' => $template->id,
            'landlord_id'       => $lease->property?->landlord_id,
            'tenant_id'         => $lease->tenant_id,
            'lease_id'          => $lease->id,
            'to_email'          => $toEmail,
            'subject_sent'      => $subject,
            'trigger_date'      => $triggerDate,
            'trigger_key'       => $triggerKey,
            'status'            => $status,
            'error_message'     => $error,
            'sent_at'           => $status === 'sent' ? now() : null,
        ]);
    }
}

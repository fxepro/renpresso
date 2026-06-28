<?php

namespace App\Jobs;

use App\Models\{Lease, Payment};
use App\Payment\ProcessorFactory;
use App\Payment\Data\WebhookEvent;
use App\Services\PaymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendRentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Payment $payment,
        private readonly int     $daysOverdue,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $lease    = $this->payment->lease;
        $tenant   = $lease->tenant;
        $landlord = $lease->property->landlord;

        // Escalation cadence: day 1 = polite, day 5 = firm, day 10 = formal
        $tone = match (true) {
            $this->daysOverdue >= 10 => 'formal',
            $this->daysOverdue >= 5  => 'firm',
            default                  => 'polite',
        };

        Log::info("Sending {$tone} rent reminder to {$tenant->email} — {$this->daysOverdue} days overdue");

        // TODO: Mail::to($tenant->email)->send(new RentReminderMail($this->payment, $tone));
        // TODO: Mail::to($landlord->email)->send(new ArrearNoticeMail($this->payment, $this->daysOverdue));
    }
}

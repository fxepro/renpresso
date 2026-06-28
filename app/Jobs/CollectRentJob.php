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

class CollectRentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(private readonly Lease $lease)
    {
        $this->onQueue('payments');
    }

    public function handle(PaymentService $paymentService): void
    {
        try {
            $paymentService->collectRent($this->lease);
        } catch (\Exception $e) {
            Log::error("Rent collection failed [lease:{$this->lease->id}]: {$e->getMessage()}");
            throw $e; // will retry
        }
    }
}

// ─────────────────────────────────────────────────────────────
// SEND RENT REMINDER JOB
// Dispatched when payment is overdue.
// ─────────────────────────────────────────────────────────────

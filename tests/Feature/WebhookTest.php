<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function webhook_endpoint_exists_for_each_processor()
    {
        $processors = ['stripe', 'razorpay', 'flutterwave', 'xendit', 'mercadopago'];

        foreach ($processors as $processor) {
            // Without valid signature, expect 401 not 404
            $response = $this->post("/webhooks/{$processor}", [], [
                'Content-Type' => 'application/json',
            ]);

            $this->assertNotEquals(404, $response->status(),
                "Webhook endpoint missing for processor: {$processor}");
        }
    }

    /** @test */
    public function unknown_processor_returns_404()
    {
        $response = $this->post('/webhooks/unknown-processor');
        $response->assertStatus(404);
    }
}

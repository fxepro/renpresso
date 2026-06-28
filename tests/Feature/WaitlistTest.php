<?php

namespace Tests\Feature;

use App\Mail\WaitlistConfirmationMail;
use App\Models\WaitlistEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    private function waitlistPayload(string $email = 'test@example.com'): array
    {
        return [
            'email'              => $email,
            'first_name'         => 'Alex',
            'last_name'          => 'Johnson',
            'home_country'       => 'CA',
            'property_countries' => 'Ontario',
        ];
    }

    public function test_it_stores_a_waitlist_email(): void
    {
        Mail::fake();

        $response = $this->post('/waitlist', $this->waitlistPayload());

        $response->assertRedirect();
        $this->assertDatabaseHas('waitlist_emails', ['email' => 'test@example.com']);
        Mail::assertSent(WaitlistConfirmationMail::class, fn ($mail) => $mail->hasTo('test@example.com'));
    }

    public function test_it_keeps_internal_ref_without_showing_it_to_users(): void
    {
        Mail::fake();

        $this->post('/waitlist', $this->waitlistPayload());

        $entry = WaitlistEmail::where('email', 'test@example.com')->first();
        $this->assertNotNull($entry->ref);
        $this->assertStringStartsWith('RMX-', $entry->ref);
    }

    public function test_it_does_not_duplicate_on_second_submission(): void
    {
        Mail::fake();

        $this->post('/waitlist', $this->waitlistPayload());
        $this->post('/waitlist', $this->waitlistPayload());

        $this->assertEquals(1, WaitlistEmail::where('email', 'test@example.com')->count());
    }

    public function test_it_rejects_invalid_email(): void
    {
        $response = $this->post('/waitlist', array_merge($this->waitlistPayload('not-an-email'), [
            'email' => 'not-an-email',
        ]));

        $response->assertSessionHasErrors('email');
    }
}

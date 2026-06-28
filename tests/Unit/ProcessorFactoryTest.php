<?php

namespace Tests\Unit;

use App\Payment\ProcessorFactory;
use App\Payment\Processors\StripeProcessor;
use App\Payment\Processors\RazorpayProcessor;
use App\Payment\Processors\FlutterwaveProcessor;
use App\Payment\Processors\XenditProcessor;
use App\Payment\Processors\MercadoPagoProcessor;
use InvalidArgumentException;
use Tests\TestCase;

class ProcessorFactoryTest extends TestCase
{
    /** @test */
    public function it_resolves_stripe_for_us()
    {
        $processor = ProcessorFactory::for('US');
        $this->assertInstanceOf(StripeProcessor::class, $processor);
    }

    /** @test */
    public function it_resolves_stripe_for_european_countries()
    {
        foreach (['FR', 'DE', 'GB', 'IT', 'ES', 'NL', 'AU'] as $country) {
            $processor = ProcessorFactory::for($country);
            $this->assertInstanceOf(StripeProcessor::class, $processor, "Failed for {$country}");
        }
    }

    /** @test */
    public function it_resolves_razorpay_for_india()
    {
        $processor = ProcessorFactory::for('IN');
        $this->assertInstanceOf(RazorpayProcessor::class, $processor);
    }

    /** @test */
    public function it_resolves_flutterwave_for_africa()
    {
        foreach (['NG', 'KE', 'GH', 'ZA'] as $country) {
            $processor = ProcessorFactory::for($country);
            $this->assertInstanceOf(FlutterwaveProcessor::class, $processor, "Failed for {$country}");
        }
    }

    /** @test */
    public function it_resolves_xendit_for_southeast_asia()
    {
        foreach (['ID', 'PH', 'MY', 'VN', 'TH'] as $country) {
            $processor = ProcessorFactory::for($country);
            $this->assertInstanceOf(XenditProcessor::class, $processor, "Failed for {$country}");
        }
    }

    /** @test */
    public function it_resolves_mercadopago_for_latam()
    {
        foreach (['BR', 'MX', 'AR', 'CO', 'CL'] as $country) {
            $processor = ProcessorFactory::for($country);
            $this->assertInstanceOf(MercadoPagoProcessor::class, $processor, "Failed for {$country}");
        }
    }

    /** @test */
    public function it_throws_for_unsupported_country()
    {
        $this->expectException(InvalidArgumentException::class);
        ProcessorFactory::for('XX');
    }

    /** @test */
    public function it_is_case_insensitive()
    {
        $upper = ProcessorFactory::for('IN');
        $lower = ProcessorFactory::for('in');
        $this->assertInstanceOf(get_class($upper), $lower);
    }

    /** @test */
    public function it_returns_supported_countries()
    {
        $countries = ProcessorFactory::supportedCountries();
        $this->assertContains('US', $countries);
        $this->assertContains('IN', $countries);
        $this->assertContains('NG', $countries);
        $this->assertNotEmpty($countries);
    }

    /** @test */
    public function it_correctly_reports_support()
    {
        $this->assertTrue(ProcessorFactory::supports('US'));
        $this->assertTrue(ProcessorFactory::supports('IN'));
        $this->assertFalse(ProcessorFactory::supports('XX'));
    }
}

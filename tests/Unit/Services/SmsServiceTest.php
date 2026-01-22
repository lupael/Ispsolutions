<?php

namespace Tests\Unit\Services;

use App\Services\SmsService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SmsServiceTest extends TestCase
{
    protected SmsService $smsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->smsService = new SmsService;

        // Fake HTTP requests
        Http::fake();
    }

    public function test_can_send_sms_when_enabled()
    {
        Config::set('sms.enabled', true);
        Config::set('sms.default_gateway', 'twilio');

        $result = $this->smsService->send('01712345678', 'Test message');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_skips_sending_when_sms_disabled()
    {
        Config::set('sms.enabled', false);

        $result = $this->smsService->send('01712345678', 'Test message');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('disabled', $result['message']);
    }

    public function test_can_send_otp()
    {
        Config::set('sms.enabled', true);

        $result = $this->smsService->sendOtp('01712345678', '123456');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_send_invoice_notification()
    {
        Config::set('sms.enabled', true);

        $result = $this->smsService->sendInvoiceNotification('01712345678', 'INV-001', 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_send_payment_confirmation()
    {
        Config::set('sms.enabled', true);

        $result = $this->smsService->sendPaymentConfirmation('01712345678', 1000, 'INV-001');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_send_expiration_reminder()
    {
        Config::set('sms.enabled', true);

        $result = $this->smsService->sendExpirationReminder('01712345678', 3);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_can_send_overdue_notification()
    {
        Config::set('sms.enabled', true);

        $result = $this->smsService->sendOverdueNotification('01712345678', 'INV-001', 1000);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    public function test_handles_invalid_mobile_number()
    {
        Config::set('sms.enabled', true);

        $result = $this->smsService->send('invalid', 'Test message');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }
}

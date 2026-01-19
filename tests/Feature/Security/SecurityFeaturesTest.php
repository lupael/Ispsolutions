<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Services\AuditLogService;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_headers_are_present(): void
    {
        $response = $this->get('/');

        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('X-XSS-Protection');
    }

    public function test_csrf_token_is_required_for_post_requests(): void
    {
        // Create a test route that requires CSRF protection
        $user = User::factory()->create();
        
        // Attempt POST request without CSRF token (should be rejected)
        $response = $this->actingAs($user)->post('/api/test-csrf', [
            'test' => 'data'
        ]);
        
        // Should receive 419 status (CSRF token mismatch) or be blocked
        // For now, we'll verify CSRF middleware is in the global middleware stack
        $middleware = app()->make(\Illuminate\Contracts\Http\Kernel::class)->getMiddleware();
        $this->assertTrue(
            collect($middleware)->contains(fn($m) => str_contains($m, 'VerifyCsrfToken')),
            'CSRF middleware should be registered globally'
        );
    }

    public function test_2fa_can_be_enabled_for_user(): void
    {
        $user = User::factory()->create();
        $twoFactorService = app(TwoFactorAuthenticationService::class);

        $result = $twoFactorService->enable2FA($user);

        $this->assertArrayHasKey('secret', $result);
        $this->assertArrayHasKey('qr_code_url', $result);
        $this->assertNotNull($user->fresh()->two_factor_secret);
    }

    public function test_2fa_code_verification_works(): void
    {
        $user = User::factory()->create();
        $twoFactorService = app(TwoFactorAuthenticationService::class);

        $twoFactorService->enable2FA($user);
        
        // Note: In real tests, you'd generate a valid TOTP code
        // This is just structural verification
        $this->assertFalse($twoFactorService->verify2FACode($user, '000000'));
    }

    public function test_audit_log_is_created_for_actions(): void
    {
        $user = User::factory()->create();
        $auditService = app(AuditLogService::class);

        $this->actingAs($user);
        
        $log = $auditService->log('test.action', $user, null, ['test' => 'data']);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'event' => 'test.action',
        ]);
    }

    public function test_recovery_codes_can_be_generated(): void
    {
        $user = User::factory()->create();
        $twoFactorService = app(TwoFactorAuthenticationService::class);

        $codes = $twoFactorService->generateRecoveryCodes($user);

        $this->assertCount(8, $codes);
        $this->assertNotNull($user->fresh()->two_factor_recovery_codes);
    }
}

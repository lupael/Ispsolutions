<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\TwoFactorAuthenticationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TwoFactorAuthController extends Controller
{
    protected TwoFactorAuthenticationService $twoFactorService;

    public function __construct(TwoFactorAuthenticationService $twoFactorService)
    {
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Show 2FA setup page.
     */
    public function index(): View
    {
        $user = auth()->user();
        $isEnabled = $this->twoFactorService->isEnabled($user);
        $recoveryCodesCount = $this->twoFactorService->getRemainingRecoveryCodesCount($user);

        return view('panels.shared.2fa.index', compact('isEnabled', 'recoveryCodesCount'));
    }

    /**
     * Enable 2FA for user.
     */
    public function enable(): View
    {
        $user = auth()->user();
        $setup = $this->twoFactorService->enable2FA($user);

        return view('panels.shared.2fa.setup', [
            'secret' => $setup['secret'],
            'qrCodeUrl' => $setup['qr_code_url'],
        ]);
    }

    /**
     * Verify and activate 2FA.
     */
    public function verify(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = auth()->user();

        if ($this->twoFactorService->verifyAndEnable($user, $request->code)) {
            // Generate recovery codes
            $recoveryCodes = $this->twoFactorService->generateRecoveryCodes($user);

            return redirect()
                ->route('2fa.recovery-codes')
                ->with('recovery_codes', $recoveryCodes)
                ->with('success', 'Two-factor authentication enabled successfully!');
        }

        return back()->withErrors(['code' => 'Invalid verification code.']);
    }

    /**
     * Show recovery codes.
     */
    public function recoveryCodes(): View
    {
        $recoveryCodes = session('recovery_codes', []);

        return view('panels.shared.2fa.recovery-codes', compact('recoveryCodes'));
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(): RedirectResponse
    {
        $user = auth()->user();

        if (! $this->twoFactorService->isEnabled($user)) {
            return back()->withErrors(['error' => '2FA is not enabled.']);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes($user);

        return redirect()
            ->route('2fa.recovery-codes')
            ->with('recovery_codes', $recoveryCodes)
            ->with('success', 'Recovery codes regenerated successfully!');
    }

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|current_password',
        ]);

        $user = auth()->user();
        $this->twoFactorService->disable2FA($user);

        return redirect()
            ->route('2fa.index')
            ->with('success', 'Two-factor authentication disabled successfully!');
    }
}

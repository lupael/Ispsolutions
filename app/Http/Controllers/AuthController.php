<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Redirect based on user role
            return $this->redirectToDashboard();
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /**
     * Redirect user to appropriate dashboard based on their role.
     */
    protected function redirectToDashboard(): RedirectResponse
    {
        $user = Auth::user();

        // Check user roles and redirect accordingly
        if ($user->hasRole('super-admin')) {
            return redirect()->route('panel.super-admin.dashboard');
        }

        if ($user->hasRole('admin')) {
            return redirect()->route('panel.admin.dashboard');
        }

        if ($user->hasRole('developer')) {
            return redirect()->route('panel.developer.dashboard');
        }

        if ($user->hasRole('manager')) {
            return redirect()->route('panel.manager.dashboard');
        }

        if ($user->hasRole('reseller')) {
            return redirect()->route('panel.reseller.dashboard');
        }

        if ($user->hasRole('sub-reseller')) {
            return redirect()->route('panel.sub-reseller.dashboard');
        }

        if ($user->hasRole('card-distributor')) {
            return redirect()->route('panel.card-distributor.dashboard');
        }

        if ($user->hasRole('staff')) {
            return redirect()->route('panel.staff.dashboard');
        }

        if ($user->hasRole('customer')) {
            return redirect()->route('panel.customer.dashboard');
        }

        // Default fallback
        return redirect('/');
    }
}

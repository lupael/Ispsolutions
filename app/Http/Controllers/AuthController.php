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

        // Role to route mapping
        $roleRoutes = [
            'super-admin' => 'panel.super-admin.dashboard',
            'admin' => 'panel.admin.dashboard',
            'developer' => 'panel.developer.dashboard',
            'manager' => 'panel.manager.dashboard',
            'reseller' => 'panel.reseller.dashboard',
            'sub-reseller' => 'panel.sub-reseller.dashboard',
            'card-distributor' => 'panel.card-distributor.dashboard',
            'staff' => 'panel.staff.dashboard',
            'customer' => 'panel.customer.dashboard',
        ];

        // Check user roles and redirect accordingly
        foreach ($roleRoutes as $role => $route) {
            if ($user->hasRole($role)) {
                return redirect()->route($route);
            }
        }

        // Default fallback
        return redirect('/');
    }
}

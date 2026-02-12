<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laragear\WebAuthn\WebAuthn;
use Illuminate\Contracts\Auth\Authenticatable;
use Laragear\WebAuthn\Exceptions\WebAuthnException;

class WebAuthnController extends Controller
{
    /**
     * Shows the WebAuthn management view.
     */
    public function index()
    {
        return view('auth.webauthn');
    }

    /**
     * Generates the options for creating a new WebAuthn credential.
     */
    public function generateCreationOptions(Request $request)
    {
        return WebAuthn::generateAttestation($request->user());
    }

    /**
     * Stores a new WebAuthn credential.
     */
    public function storeCredential(Request $request)
    {
        try {
            WebAuthn::storeAttestation($request->all(), $request->user());

            return back()->with('success', 'Security key added successfully.');
        } catch (WebAuthnException $e) {
            return back()->withErrors(['webauthn' => $e->getMessage()]);
        }
    }

    /**
     * Generates the options for asserting a WebAuthn credential.
     */
    public function generateAssertionOptions(Request $request)
    {
        return WebAuthn::generateAssertion($request->user());
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * Language Switcher Controller
 * Task 6.2: Add language switcher to UI
 */
class LanguageController extends Controller
{
    /**
     * Available languages in the system
     */
    private const AVAILABLE_LANGUAGES = ['en', 'bn'];

    /**
     * Switch application language
     */
    public function switch(Request $request): RedirectResponse
    {
        $language = $request->input('language', 'en');

        // Validate language code
        if (!in_array($language, self::AVAILABLE_LANGUAGES)) {
            return redirect()->back()->with('error', 'Invalid language selected.');
        }

        // Store language preference in session
        Session::put('locale', $language);

        // If user is authenticated, store preference in database
        $user = Auth::user();
        if ($user && method_exists($user, 'update')) {
            $user->update(['language' => $language]);
        }

        // Set application locale
        App::setLocale($language);

        return redirect()->back()->with('success', __('messages.language_changed'));
    }
}

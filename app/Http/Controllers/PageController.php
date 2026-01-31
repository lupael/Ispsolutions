<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller for public pages like Privacy Policy and Terms of Service
 */
class PageController extends Controller
{
    /**
     * Display the privacy policy page
     */
    public function privacyPolicy(): View
    {
        return view('pages.privacy-policy');
    }

    /**
     * Display the terms of service page
     */
    public function termsOfService(): View
    {
        return view('pages.terms-of-service');
    }

    /**
     * Display the support/contact page
     */
    public function support(): View
    {
        return view('pages.support');
    }
}

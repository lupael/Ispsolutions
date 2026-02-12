<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotspotLoginController extends Controller
{
    public function showLoginForm() {}
    public function requestLoginOtp() {}
    public function showVerifyLoginOtp() {}
    public function verifyLoginOtp() {}
    public function showDeviceConflict() {}
    public function forceLogin() {}
    public function processLinkLogin($token) {}
    public function federatedLogin() {}
    public function showDashboard() {}
    public function logout() {}
    public function showLinkDashboard() {}
    public function generateLinkLogin() {}
}

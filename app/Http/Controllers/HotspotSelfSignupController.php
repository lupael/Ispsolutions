<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HotspotSelfSignupController extends Controller
{
    public function showRegistrationForm() {}
    public function requestOtp() {}
    public function showVerifyOtp() {}
    public function verifyOtp() {}
    public function resendOtp() {}
    public function showCompleteProfile() {}
    public function completeRegistration() {}
    public function showPaymentPage($user) {}
    public function processPayment($user) {}
    public function paymentCallback() {}
    public function showSuccess() {}
    public function showError() {}
}

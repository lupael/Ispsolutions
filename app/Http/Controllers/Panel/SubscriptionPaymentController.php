<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SubscriptionPaymentController extends Controller
{
    public function index() {}
    public function show($plan) {}
    public function subscribe($plan) {}
    public function bills() {}
    public function processPayment($bill) {}
    public function cancel() {}
}

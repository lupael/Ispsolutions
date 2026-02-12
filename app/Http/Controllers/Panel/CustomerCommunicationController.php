<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerCommunicationController extends Controller
{
    public function showSmsForm($customer) {}
    public function sendSms($customer) {}
    public function showPaymentLinkForm($customer) {}
    public function sendPaymentLink($customer) {}
}

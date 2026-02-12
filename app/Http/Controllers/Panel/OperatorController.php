<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function dashboard() {}
    public function subOperators() {}
    public function customers() {}
    public function showCustomer($customer) {}
    public function bills() {}
    public function showBill($bill) {}
    public function createPayment() {}
    public function storePayment() {}
    public function cards() {}
    public function complaints() {}
    public function showComplaint($complaint) {}
    public function reports() {}
    public function sms() {}
    public function sendSms() {}
    public function packages() {}
    public function commission() {}
}

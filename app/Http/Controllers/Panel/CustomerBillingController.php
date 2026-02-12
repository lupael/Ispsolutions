<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerBillingController extends Controller
{
    public function createBill($customer) {}
    public function storeBill($customer) {}
    public function editBillingProfile($customer) {}
    public function updateBillingProfile($customer) {}
    public function createOtherPayment($customer) {}
    public function storeOtherPayment($customer) {}
}

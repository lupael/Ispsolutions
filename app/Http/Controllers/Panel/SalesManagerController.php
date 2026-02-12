<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesManagerController extends Controller
{
    public function dashboard() {}
    public function admins() {}
    public function affiliateLeads() {}
    public function createLead() {}
    public function showLead($lead) {}
    public function salesComments() {}
    public function subscriptionBills() {}
    public function showBill($bill) {}
    public function payBill($bill) {}
    public function createSubscriptionPayment() {}
    public function pendingSubscriptionPayments() {}
    public function noticeBroadcast() {}
    public function changePassword() {}
    public function secureLogin() {}
}

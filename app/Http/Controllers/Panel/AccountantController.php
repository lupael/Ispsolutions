<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountantController extends Controller
{
    public function dashboard() {}
    public function incomeExpenseReport() {}
    public function paymentHistory() {}
    public function customerStatements() {}
    public function transactions() {}
    public function expenses() {}
    public function vatCollections() {}
    public function paymentsHistory() {}
    public function customerStatement($customer) {}
}

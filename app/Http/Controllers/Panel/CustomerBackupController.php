<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerBackupController extends Controller
{
    public function backupCustomer($customer) {}
    public function backupAllCustomers($router) {}
    public function removeCustomer($customer) {}
}

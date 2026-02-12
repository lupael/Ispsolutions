<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModalController extends Controller
{
    public function showFup($package) {}
    public function showBillingProfile($profileId) {}
    public function showQuickAction($customer, $action) {}
    public function executeQuickAction($customer, $action) {}
}

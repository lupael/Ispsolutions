<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SuperAdminController extends Controller
{
    public function dashboard() {}
    public function users() {}
    public function usersCreate() {}
    public function usersStore() {}
    public function usersEdit($id) {}
    public function usersUpdate($id) {}
    public function usersDestroy($id) {}
    public function roles() {}
    public function ispIndex() {}
    public function ispCreate() {}
    public function ispStore() {}
    public function ispEdit($id) {}
    public function ispUpdate($id) {}
    public function billingFixed() {}
    public function billingFixedStore() {}
    public function billingUserBase() {}
    public function billingUserBaseStore() {}
    public function billingPanelBase() {}
    public function billingPanelBaseStore() {}
    public function paymentGatewayIndex() {}
    public function paymentGatewayCreate() {}
    public function paymentGatewaySettings() {}
    public function paymentGatewayStore() {}
    public function paymentGatewayEdit($id) {}
    public function paymentGatewayUpdate($id) {}
    public function paymentGatewayDestroy($id) {}
    public function smsGatewayIndex() {}
    public function smsGatewayCreate() {}
    public function smsGatewayStore() {}
    public function smsGatewayEdit($id) {}
    public function smsGatewayUpdate($id) {}
    public function smsGatewayDestroy($id) {}
    public function logs() {}
    public function settings() {}
    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($id) {}
    public function edit($id) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}

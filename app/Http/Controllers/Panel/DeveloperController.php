<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeveloperController extends Controller
{
    public function dashboard() {}
    public function tenancies() {}
    public function createTenancy() {}
    public function storeTenancy() {}
    public function editTenancy($id) {}
    public function updateTenancy($id) {}
    public function toggleTenancyStatus($tenant) {}
    public function allAdmins() {}
    public function showAdmin($id) {}
    public function subscriptionPlans() {}
    public function createSubscription() {}
    public function storeSubscription() {}
    public function editSubscription($id) {}
    public function updateSubscription($id) {}
    public function deleteSubscription($id) {}
    public function paymentGateways() {}
    public function createPaymentGateway() {}
    public function storePaymentGateway() {}
    public function editPaymentGateway($id) {}
    public function updatePaymentGateway($id) {}
    public function deletePaymentGateway($id) {}
    public function smsGateways() {}
    public function createSmsGateway() {}
    public function storeSmsGateway() {}
    public function editSmsGateway($id) {}
    public function updateSmsGateway($id) {}
    public function deleteSmsGateway($id) {}
    public function vpnPools() {}
    public function accessPanel() {}
    public function searchCustomers() {}
    public function allCustomers() {}
    public function showCustomer($id) {}
    public function auditLogs() {}
    public function logs() {}
    public function errorLogs() {}
    public function clearErrorLogs() {}
    public function apiDocs() {}
    public function apiKeys() {}
    public function settings() {}
    public function debug() {}
    public function impersonate($user) {}
    public function stopImpersonating() {}
    public function showRoleSettings() {}
    public function updateRoleNames() {}
    public function showSubscriptionFeatures() {}
    public function updateSubscriptionFeatures() {}
    public function updateSubscriptionLimits() {}

    public function index() {}
    public function create() {}
    public function store(Request $request) {}
    public function show($id) {}
    public function edit($id) {}
    public function update(Request $request, $id) {}
    public function destroy($id) {}
}

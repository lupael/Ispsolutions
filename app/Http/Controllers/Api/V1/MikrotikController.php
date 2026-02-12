<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    public function listRouters() {}
    public function connectRouter($id) {}
    public function healthCheck($id) {}
    public function listPppoeUsers() {}
    public function createPppoeUser() {}
    public function updatePppoeUser($username) {}
    public function deletePppoeUser($username) {}
    public function listActiveSessions() {}
    public function disconnectSession($id) {}
    public function listProfiles() {}
    public function createProfile() {}
    public function viewProfile($id) {}
    public function updateProfile($id) {}
    public function deleteProfile($id) {}
    public function importProfiles($routerId) {}
    public function listIpPools() {}
    public function createIpPool() {}
    public function viewIpPool($id) {}
    public function updateIpPool($id) {}
    public function deleteIpPool($id) {}
    public function importIpPools($routerId) {}
    public function importSecrets($routerId) {}
    public function configureRouter($routerId) {}
    public function listConfigurations($routerId) {}
    public function listVpnAccounts() {}
    public function createVpnAccount() {}
    public function viewVpnAccount($id) {}
    public function updateVpnAccount($id) {}
    public function deleteVpnAccount($id) {}
    public function getVpnStatus($routerId) {}
    public function listQueues() {}
    public function createQueue() {}
    public function viewQueue($id) {}
    public function updateQueue($id) {}
    public function deleteQueue($id) {}
    public function listFirewallRules($routerId) {}
    public function addFirewallRule() {}
    public function listPackageMappings() {}
    public function mapPackageToProfile() {}
    public function viewPackageMapping($id) {}
    public function updatePackageMapping($id) {}
    public function deletePackageMapping($id) {}
    public function applySpeedToUser($userId) {}
}

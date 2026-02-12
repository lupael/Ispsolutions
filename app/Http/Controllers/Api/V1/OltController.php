<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OltController extends Controller
{
    public function index() {}
    public function allBackups() {}
    public function show($id) {}
    public function testConnection($id) {}
    public function syncOnus($id) {}
    public function statistics($id) {}
    public function createBackup($id) {}
    public function backups($id) {}
    public function portUtilization($id) {}
    public function bandwidthUsage($id) {}
    public function monitorOnus($id) {}
    public function snmpTraps() {}
    public function acknowledgeAllTraps() {}
    public function acknowledgeTrap($trapId) {}
    public function onuDetails($onuId) {}
    public function refreshOnuStatus($onuId) {}
    public function authorizeOnu($onuId) {}
    public function unauthorizeOnu($onuId) {}
    public function rebootOnu($onuId) {}
    public function bulkOnuOperations() {}
}

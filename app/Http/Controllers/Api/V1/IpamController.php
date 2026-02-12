<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IpamController extends Controller
{
    public function listPools() {}
    public function createPool() {}
    public function getPool($id) {}
    public function updatePool($id) {}
    public function deletePool($id) {}
    public function listSubnets() {}
    public function createSubnet() {}
    public function getSubnet($id) {}
    public function updateSubnet($id) {}
    public function deleteSubnet($id) {}
    public function listAllocations() {}
    public function allocateIP() {}
    public function releaseIP($id) {}
    public function getPoolUtilization($id) {}
    public function getAvailableIPs($id) {}
}

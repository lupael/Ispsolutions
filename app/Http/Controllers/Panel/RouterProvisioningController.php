<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RouterProvisioningController extends Controller
{
    public function index() {}
    public function show($routerId) {}
    public function preview() {}
    public function provision() {}
    public function testConnection() {}
    public function backup() {}
    public function rollback() {}
    public function provisionRadius() {}
    public function exportPppSecrets() {}
    public function logs($routerId) {}
    public function backups($routerId) {}
    public function templates() {}
    public function createTemplate() {}
    public function storeTemplate() {}
    public function getTemplate($templateId) {}
}

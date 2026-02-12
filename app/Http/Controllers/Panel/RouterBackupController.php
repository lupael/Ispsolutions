<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RouterBackupController extends Controller
{
    public function index() {}
    public function show($routerId) {}
    public function create($routerId) {}
    public function list($routerId) {}
    public function restore($routerId) {}
    public function destroy($routerId, $backupId) {}
    public function cleanup($routerId) {}
}

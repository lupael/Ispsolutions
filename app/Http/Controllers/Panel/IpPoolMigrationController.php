<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IpPoolMigrationController extends Controller
{
    public function index() {}
    public function validateMigration() {}
    public function start() {}
    public function progress($migrationId) {}
    public function rollback($migrationId) {}
    public function validate() {}
}

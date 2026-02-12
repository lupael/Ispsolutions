<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function dashboard() {}
    public function sessions() {}
    public function showSession($session) {}
    public function disconnectSession($session) {}
    public function reports() {}
    public function customers() {}
    public function payments() {}
    public function complaints() {}
}

<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RouterFailoverController extends Controller
{
    public function index() {}
    public function show($routerId) {}
    public function configure($routerId) {}
    public function switchToRadius($routerId) {}
    public function switchToRouter($routerId) {}
    public function status($routerId) {}
    public function testConnection($routerId) {}
}

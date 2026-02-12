<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RouterConfigurationController extends Controller
{
    public function index() {}
    public function show($routerId) {}
    public function configureRadius($routerId) {}
    public function configurePpp($routerId) {}
    public function configureFirewall($routerId) {}
    public function radiusStatus($routerId) {}
}

<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index() {}
    public function preferences() {}
    public function updatePreferences() {}
    public function markAsRead($notification) {}
    public function markAllAsRead() {}
}

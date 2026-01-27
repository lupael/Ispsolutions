<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ValidationController extends Controller
{
    /**
     * Check if mobile number already exists
     */
    public function checkMobile(Request $request): JsonResponse
    {
        $mobile = $request->input('mobile');
        $excludeId = $request->input('exclude_id'); // For edit forms
        
        if (empty($mobile)) {
            return response()->json(['exists' => false]);
        }

        $query = User::where('mobile', $mobile);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // Scope to tenant for security
        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This mobile number is already registered' : 'Mobile number is available'
        ]);
    }

    /**
     * Check if username already exists (now uses User model with operator_level = 100).
     * Note: Migrated from NetworkUser to User model.
     */
    public function checkUsername(Request $request): JsonResponse
    {
        $username = $request->input('username');
        $excludeId = $request->input('exclude_id');
        
        if (empty($username)) {
            return response()->json(['exists' => false]);
        }

        $query = User::where('username', $username)
            ->where('operator_level', 100);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // Scope to tenant
        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This username is already taken' : 'Username is available'
        ]);
    }

    /**
     * Check if email already exists
     */
    public function checkEmail(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $excludeId = $request->input('exclude_id');
        
        if (empty($email)) {
            return response()->json(['exists' => false]);
        }

        $query = User::where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // Scope to tenant
        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This email is already registered' : 'Email is available'
        ]);
    }

    /**
     * Check if national ID/document number already exists
     */
    public function checkNationalId(Request $request): JsonResponse
    {
        $nationalId = $request->input('national_id');
        $excludeId = $request->input('exclude_id');
        
        if (empty($nationalId)) {
            return response()->json(['exists' => false]);
        }

        $query = User::where('national_id', $nationalId);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        // Scope to tenant
        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This national ID is already registered' : 'National ID is available'
        ]);
    }

    /**
     * Check if static IP already exists
     */
    public function checkStaticIp(Request $request): JsonResponse
    {
        $ipAddress = $request->input('ip_address');
        $excludeId = $request->input('exclude_id');
        
        if (empty($ipAddress)) {
            return response()->json(['exists' => false]);
        }

        // Check if ip_allocations table exists
        if (!Schema::hasTable('ip_allocations')) {
            return response()->json([
                'exists' => false,
                'message' => 'IP allocation tracking not configured'
            ]);
        }

        // Check in IP allocations table with tenant scoping
        $query = DB::table('ip_allocations')
            ->where('ip_address', $ipAddress)
            ->where('status', 'allocated');
        
        // Scope to tenant for security
        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This IP address is already allocated' : 'IP address is available'
        ]);
    }
}

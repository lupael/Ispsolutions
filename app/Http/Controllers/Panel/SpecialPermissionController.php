<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\SpecialPermission;
use App\Models\User;
use App\Services\SpecialPermissionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SpecialPermissionController extends Controller
{
    // Role level constants matching SpecialPermissionPolicy
    private const ADMIN_LEVEL = 20;
    private const OPERATOR_LEVEL = 30;
    private const SUB_OPERATOR_LEVEL = 40;

    private SpecialPermissionService $permissionService;

    public function __construct(SpecialPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', SpecialPermission::class);

        $query = SpecialPermission::with(['user', 'grantedBy'])
            ->where('tenant_id', auth()->user()->tenant_id);

        // Filter by user if specified
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by permission key
        if ($request->filled('permission_key')) {
            $query->where('permission_key', $request->permission_key);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        $permissions = $query->orderBy('granted_at', 'desc')->paginate(20);
        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('role_level', [self::ADMIN_LEVEL, self::OPERATOR_LEVEL, self::SUB_OPERATOR_LEVEL])
            ->orderBy('name')
            ->get();

        $availablePermissions = $this->permissionService->getAvailablePermissions();

        return view('panels.admin.special-permissions.index', compact('permissions', 'users', 'availablePermissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', SpecialPermission::class);

        $users = User::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('role_level', [self::ADMIN_LEVEL, self::OPERATOR_LEVEL, self::SUB_OPERATOR_LEVEL])
            ->orderBy('name')
            ->get();

        $availablePermissions = $this->permissionService->getAvailablePermissions();

        return view('panels.admin.special-permissions.create', compact('users', 'availablePermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Gate::authorize('create', SpecialPermission::class);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_key' => 'required|string',
            'resource_type' => 'nullable|string',
            'resource_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $expiresAt = $validated['expires_at'] ? Carbon::parse($validated['expires_at']) : null;

        $this->permissionService->grantPermission(
            user: $user,
            permissionKey: $validated['permission_key'],
            resourceType: $validated['resource_type'] ?? null,
            resourceId: $validated['resource_id'] ?? null,
            expiresAt: $expiresAt,
            description: $validated['description'] ?? null,
            grantedBy: auth()->user()
        );

        return redirect()->route('panel.admin.special-permissions.index')
            ->with('success', 'Special permission granted successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpecialPermission $specialPermission)
    {
        Gate::authorize('delete', $specialPermission);

        $this->permissionService->revokePermission($specialPermission);

        return redirect()->route('panel.admin.special-permissions.index')
            ->with('success', 'Special permission revoked successfully.');
    }
}

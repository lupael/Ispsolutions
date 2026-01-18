<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BelongsToTenant, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'service_package_id',
        'is_active',
        'activated_at',
        'created_by',
        'operator_level',
        'disabled_menus',
        'manager_id',
        'operator_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'activated_at' => 'datetime',
            'disabled_menus' => 'array',
        ];
    }

    public function servicePackage(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class);
    }

    /**
     * Alias for servicePackage() for convenience.
     */
    public function package(): BelongsTo
    {
        return $this->servicePackage();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ipAllocations(): HasMany
    {
        return $this->hasMany(IpAllocation::class);
    }

    public function radiusSessions(): HasMany
    {
        return $this->hasMany(RadiusSession::class);
    }

    /**
     * Get the roles for the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasAnyRole(array $roleSlugs): bool
    {
        return $this->roles()->whereIn('slug', $roleSlugs)->exists();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        /** @var Role $role */
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    public function currentPackage(): ?ServicePackage
    {
        // Eager load the relationship if not already loaded
        if (! $this->relationLoaded('servicePackage')) {
            $this->load('servicePackage');
        }

        // Cast the relationship result to ServicePackage or null
        $package = $this->servicePackage;

        return $package instanceof ServicePackage ? $package : null;
    }

    /**
     * Get the supervisor (manager) of this user.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get subordinates reporting to this user.
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /**
     * Get the manager of this user.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get operator permissions for this user.
     */
    public function operatorPermissions(): HasMany
    {
        return $this->hasMany(\App\Models\OperatorPermission::class);
    }

    /**
     * Check if user has a special permission.
     */
    public function hasSpecialPermission(string $permission): bool
    {
        return $this->operatorPermissions()
            ->where('permission_key', $permission)
            ->where('is_enabled', true)
            ->exists();
    }

    /**
     * Check if a menu is disabled for this user.
     */
    public function isMenuDisabled(string $menuKey): bool
    {
        $disabledMenus = $this->disabled_menus ?? [];

        return in_array($menuKey, $disabledMenus);
    }

    /**
     * Get operator type label.
     */
    public function getOperatorTypeLabel(): string
    {
        return match ($this->operator_type) {
            'super_admin' => 'Super Admin',
            'group_admin' => 'Group Admin (ISP)',
            'operator' => 'Operator',
            'sub_operator' => 'Sub-Operator',
            'manager' => 'Manager',
            'card_distributor' => 'Card Distributor',
            'developer' => 'Developer',
            'accountant' => 'Accountant',
            default => 'User',
        };
    }

    /**
     * Check if user is an operator (staff member).
     * 
     * According to config/operators_permissions.php, level 100 represents a customer.
     * This method treats only users with operator_level < 100 as operators,
     * so level 100 users (customers) are NOT considered operators.
     */
    public function isOperator(): bool
    {
        return $this->operator_level < 100;
    }

    /**
     * Check if user is a Developer (level 0).
     * Uses operator_level as the primary source of truth.
     */
    public function isDeveloper(): bool
    {
        return $this->operator_level === 0;
    }

    /**
     * Check if user is a Super Admin (level 10).
     * Uses operator_level as the primary source of truth.
     */
    public function isSuperAdmin(): bool
    {
        return $this->operator_level === 10;
    }

    /**
     * Check if user is an Admin/Group Admin (level 20).
     * Uses operator_level as the primary source of truth.
     */
    public function isAdmin(): bool
    {
        return $this->operator_level === 20;
    }

    /**
     * Check if user is an Operator (level 30).
     * Uses operator_level as the primary source of truth.
     */
    public function isOperatorRole(): bool
    {
        return $this->operator_level === 30;
    }

    /**
     * Check if user is a Sub-Operator (level 40).
     * Uses operator_level as the primary source of truth.
     */
    public function isSubOperator(): bool
    {
        return $this->operator_level === 40;
    }

    /**
     * Check if user is a Manager (level 50).
     * Uses operator_level as the primary source of truth.
     */
    public function isManager(): bool
    {
        return $this->operator_level === 50;
    }

    /**
     * Check if user is an Accountant (level 70).
     * Uses operator_level as the primary source of truth.
     */
    public function isAccountant(): bool
    {
        return $this->operator_level === 70;
    }

    /**
     * Check if user is Staff (level 80).
     * Uses operator_level as the primary source of truth.
     */
    public function isStaff(): bool
    {
        return $this->operator_level === 80;
    }

    /**
     * Check if user is a Customer (level 100).
     * Uses operator_level as the primary source of truth.
     */
    public function isCustomer(): bool
    {
        return $this->operator_level === 100;
    }

    /**
     * Check if user can manage another user based on operator level hierarchy.
     * Lower level numbers = higher privilege.
     * Ensures both users are in the same tenant (except for Developers who can manage across tenants).
     */
    public function canManage(User $otherUser): bool
    {
        // Developers (level 0) can manage users across all tenants
        if ($this->operator_level === 0) {
            return $this->operator_level < $otherUser->operator_level;
        }

        // For all other roles, ensure same tenant and lower level
        return $this->tenant_id === $otherUser->tenant_id
            && $this->operator_level < $otherUser->operator_level;
    }

    /**
     * Get users that this user can manage based on hierarchy.
     * Developers can manage users across all tenants.
     */
    public function manageableUsers()
    {
        $query = User::where('operator_level', '>', $this->operator_level)
            ->where('id', '!=', $this->id);

        // Developers (level 0) can manage users across all tenants
        if ($this->operator_level !== 0) {
            $query->where('tenant_id', $this->tenant_id);
        }

        return $query;
    }

    /**
     * Get customers created by this user.
     */
    public function createdCustomers()
    {
        return User::where('created_by', $this->id)
            ->where('operator_level', 100);
    }

    /**
     * Get all accessible customers based on role.
     * - Developer: All customers across all tenants
     * - Super Admin: All customers in own tenants
     * - Admin: All customers in ISP
     * - Operator: Own customers + sub-operator customers
     * - Sub-Operator: Only own customers
     */
    public function accessibleCustomers()
    {
        $query = User::where('operator_level', 100);

        if ($this->isDeveloper()) {
            // Developer sees all customers across all tenants
            // Bypass global tenant scope using withoutGlobalScope from BelongsToTenant trait
            return $query->withoutGlobalScope('tenant');
        } elseif ($this->isSuperAdmin()) {
            // Super Admin sees all customers in their own tenants
            // Using join for better performance with large datasets
            return $query
                ->join('tenants', 'users.tenant_id', '=', 'tenants.id')
                ->where('tenants.created_by', $this->id)
                ->select('users.*');
        } elseif ($this->isAdmin()) {
            // Admin sees all customers in their ISP (same tenant)
            return $query->where('tenant_id', $this->tenant_id);
        } elseif ($this->isOperatorRole()) {
            // Operator sees own customers + sub-operator customers
            // Using subquery with tenant_id filtering for security
            return $query->where(function ($q) {
                $q->where('created_by', $this->id)
                    ->orWhereIn('created_by', function ($sub) {
                        $sub->select('id')
                            ->from('users')
                            ->where('created_by', $this->id)
                            ->where('operator_level', 40)
                            ->where('tenant_id', $this->tenant_id);
                    });
            })->where('tenant_id', $this->tenant_id);
        } elseif ($this->isSubOperator()) {
            // Sub-operator sees only own customers
            return $query->where('created_by', $this->id)
                ->where('tenant_id', $this->tenant_id);
        } elseif ($this->isManager() || $this->isStaff() || $this->isAccountant()) {
            // Managers/Staff/Accountant see based on permissions
            // Default to tenant customers if they have view permission
            if ($this->hasPermission('customers.view')) {
                return $query->where('tenant_id', $this->tenant_id);
            }
            // No permission = no customers
            return $query->whereRaw('1 = 0'); // Empty result set
        }

        // Default: no access
        return $query->whereRaw('1 = 0'); // Empty result set
    }
}

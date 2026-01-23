<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User Model
 *
 * ROLE HIERARCHY AND TENANCY RULES:
 *
 * 1. Tenancy Definition:
 *    - A tenancy is represented by a single Super Admin account
 *    - Tenancy and Super Admin are effectively the same entity
 *    - Each tenancy contains multiple ISPs, represented by Admin accounts
 *
 * 2. Role Consolidation:
 *    - Operator (level 30): Replaces deprecated "Reseller" role
 *    - Sub-Operator (level 40): Replaces deprecated "Sub-Reseller" role
 *    - Admins can rename these roles via custom labels (e.g., "Partner", "Agent")
 *
 * 3. Tenancy Creation Rules:
 *    - Only Developer can create tenancies
 *    - When a Developer creates a tenancy, a Super Admin is automatically provisioned
 *    - Creating a Super Admin without a tenancy is impossible
 *    - When a Super Admin creates an ISP, an Admin is automatically provisioned
 *
 * 4. Role Hierarchy:
 *    - Developer (level 0): Supreme authority across all tenants
 *    - Super Admin (level 10): Manages Admins within their own tenants only
 *    - Admin (level 20): ISP Owner, manages Operators, Sub-Operators, Managers, Staff
 *    - Operator (level 30): Manages Sub-Operators and customers
 *    - Sub-Operator (level 40): Manages only their own customers
 *    - Manager (level 50): View-only scoped access
 *    - Accountant (level 70): Financial view-only access
 *    - Staff (level 80): Support staff with limited permissions
 *    - Customer (level 100): End customer with self-service access
 *
 * 5. Permission Rules:
 *    - Only Admin can add/manage NAS, OLT, Router, PPP profiles, Pools, Packages, Package Prices
 *    - If Admin provides explicit permission, Staff/Manager can view/edit/manage resources
 *    - Operators can set prices for their Sub-Operators only
 *    - Operators cannot manage or override pricing set by Admin
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use BelongsToTenant, HasFactory, Notifiable;

    /**
     * Operator level constants
     */
    public const OPERATOR_LEVEL_DEVELOPER = 0;
    public const OPERATOR_LEVEL_SUPER_ADMIN = 10;
    public const OPERATOR_LEVEL_ADMIN = 20;
    public const OPERATOR_LEVEL_OPERATOR = 30;
    public const OPERATOR_LEVEL_SUB_OPERATOR = 40;
    public const OPERATOR_LEVEL_MANAGER = 50;
    public const OPERATOR_LEVEL_ACCOUNTANT = 70;
    public const OPERATOR_LEVEL_STAFF = 80;
    public const OPERATOR_LEVEL_CUSTOMER = 100;

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
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'company_name',
        'company_address',
        'company_phone',
        'credit_limit',
        'allow_sub_operator',
        'allow_rename_package',
        'sms_charges_by',
        'sms_cost_per_unit',
        'can_manage_customers',
        'can_view_financials',
        'payment_type',
        'wallet_balance',
        'sms_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_enabled' => 'boolean',
            'allow_sub_operator' => 'boolean',
            'allow_rename_package' => 'boolean',
            'can_manage_customers' => 'boolean',
            'can_view_financials' => 'boolean',
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
     * Assign a role to the user.
     *
     * @throws \InvalidArgumentException if the role is not found
     */
    public function assignRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        
        if (! $role) {
            throw new \InvalidArgumentException("Role '{$roleSlug}' not found.");
        }

        // Check if this exact role-tenant combination already exists
        $existingPivot = $this->roles()
            ->wherePivot('role_id', $role->id)
            ->wherePivot('tenant_id', $this->tenant_id)
            ->exists();

        if (! $existingPivot) {
            $this->roles()->attach($role->id, [
                'tenant_id' => $this->tenant_id,
            ]);
        }
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
     * Get the user who created this user (for commission tracking).
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
            'admin', 'group_admin' => 'Admin', // group_admin for backward compatibility
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
     * Get the display label for the user's primary role.
     * Uses custom label if set by Admin, otherwise returns default role name.
     */
    public function getRoleDisplayLabel(): string
    {
        $role = $this->roles->first();

        if (! $role) {
            return 'No Role';
        }

        // For Operator and Sub-Operator, check for custom labels
        if (in_array($role->slug, ['operator', 'sub-operator']) && $this->tenant_id) {
            return $role->getDisplayLabel($this->tenant_id);
        }

        return $role->name;
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

        // Super Admin can only manage users in their own tenants
        if ($this->operator_level === 10) {
            // Check if the other user belongs to a tenant created by this Super Admin
            // Using a direct comparison to avoid N+1 query issues
            if ($otherUser->tenant_id) {
                $tenantCreatedBy = \App\Models\Tenant::where('id', $otherUser->tenant_id)
                    ->value('created_by');
                if ($tenantCreatedBy === $this->id) {
                    return $this->operator_level < $otherUser->operator_level;
                }
            }

            return false;
        }

        // For all other roles, ensure same tenant and lower level
        return $this->tenant_id === $otherUser->tenant_id
            && $this->operator_level < $otherUser->operator_level;
    }

    /**
     * Check if user can create a user with the specified operator level.
     * Enforces the role creation hierarchy:
     * - Developer: Can create Super Admins (level 10)
     * - Super Admin: Can create Admins (level 20) within their own tenants
     * - Admin: Can create Operators (30), Sub-Operators (40), Managers (50), Accountants (70), Staff (80), and Customers (100) within their ISP
     * - Operator: Can create Sub-Operators (level 40) and Customers (level 100)
     * - Sub-Operator: Can only create Customers (level 100)
     */
    public function canCreateUserWithLevel(int $targetLevel): bool
    {
        // Developer can create Super Admins and below
        if ($this->isDeveloper()) {
            return $targetLevel >= 10; // Can create level 10 (Super Admin) and higher
        }

        // Super Admin can create Admins and below (but not other Super Admins)
        if ($this->isSuperAdmin()) {
            return $targetLevel >= 20 && $targetLevel > $this->operator_level;
        }

        // Admin can create Operators (30), Sub-Operators (40), Managers (50), Accountants (70), Staff (80), and Customers (100)
        if ($this->isAdmin()) {
            return $targetLevel >= 30 && $targetLevel > $this->operator_level;
        }

        // Operator can create Sub-Operators and Customers
        if ($this->isOperatorRole()) {
            return in_array($targetLevel, [40, 100]) && $targetLevel > $this->operator_level;
        }

        // Sub-Operator can only create Customers
        if ($this->isSubOperator()) {
            return $targetLevel === 100;
        }

        // Managers, Staff, Accountant cannot create users
        return false;
    }

    /**
     * Get users that this user can manage based on hierarchy.
     * Developers can manage users across all tenants.
     * Super Admins can manage users in their own tenants only.
     */
    public function manageableUsers()
    {
        $query = User::where('operator_level', '>', $this->operator_level)
            ->where('id', '!=', $this->id);

        // Developers (level 0) can manage users across all tenants
        if ($this->operator_level === 0) {
            return $query;
        }

        // Super Admin can only manage users in their own tenants
        // Using subquery for better performance
        if ($this->operator_level === 10) {
            $query->whereIn('tenant_id', function ($q) {
                $q->select('id')
                    ->from('tenants')
                    ->where('created_by', $this->id);
            });

            return $query;
        }

        // For all other roles, ensure same tenant
        $query->where('tenant_id', $this->tenant_id);

        return $query;
    }

    /**
     * Check if this user can create a Super Admin.
     * Only Developers can create Super Admins.
     */
    public function canCreateSuperAdmin(): bool
    {
        return $this->isDeveloper();
    }

    /**
     * Check if this user can create an Admin.
     * Only Developers and Super Admins can create Admins.
     */
    public function canCreateAdmin(): bool
    {
        return $this->isDeveloper() || $this->isSuperAdmin();
    }

    /**
     * Check if this user can create an Operator.
     * Developers, Super Admins, and Admins can create Operators.
     */
    public function canCreateOperator(): bool
    {
        return $this->operator_level <= 20; // Developer, Super Admin, or Admin
    }

    /**
     * Check if this user can create a Sub-Operator.
     * Developers, Super Admins, Admins, and Operators can create Sub-Operators.
     */
    public function canCreateSubOperator(): bool
    {
        return $this->operator_level <= 30; // Developer, Super Admin, Admin, or Operator
    }

    /**
     * Check if this user can create a Customer.
     * All operator roles can create customers (Developer through Sub-Operator).
     */
    public function canCreateCustomer(): bool
    {
        return $this->operator_level <= 40; // Developer through Sub-Operator
    }

    /**
     * Check if this user has view-only access (Manager, Staff, Accountant).
     * These roles should not be able to create or manage users, only view based on permissions.
     */
    public function hasViewOnlyAccess(): bool
    {
        return in_array($this->operator_level, [50, 70, 80]); // Manager, Accountant, Staff
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

    /**
     * Check if two-factor authentication is enabled for the user.
     * This method abstracts the internal implementation detail of checking the secret.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return ! empty($this->two_factor_secret);
    }

    /**
     * Get operator wallet transactions.
     */
    public function walletTransactions(): HasMany
    {
        return $this->hasMany(OperatorWalletTransaction::class, 'operator_id');
    }

    /**
     * Get operator package rates.
     */
    public function packageRates(): HasMany
    {
        return $this->hasMany(OperatorPackageRate::class, 'operator_id');
    }

    /**
     * Get operator SMS rate.
     */
    public function smsRate(): HasOne
    {
        return $this->hasOne(OperatorSmsRate::class, 'operator_id');
    }
}

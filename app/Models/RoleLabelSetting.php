<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * RoleLabelSetting Model
 * 
 * Allows Admins to customize role display labels (e.g., rename "Operator" to "Partner")
 * without changing the underlying role logic or permissions.
 * 
 * Role Hierarchy Context:
 * - Operator (level 30): Previously called "Reseller" - customizable label
 * - Sub-Operator (level 40): Previously called "Sub-Reseller" - customizable label
 * 
 * @property int $id
 * @property int $tenant_id
 * @property string $role_slug Role slug (e.g., 'operator', 'sub-operator')
 * @property string $custom_label Custom display label (e.g., 'Partner', 'Agent')
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class RoleLabelSetting extends Model
{
    use BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'role_slug',
        'custom_label',
    ];

    /**
     * Get the tenant that owns this setting.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get custom label for a role slug in a specific tenant.
     * Returns null if no custom label is set.
     */
    public static function getCustomLabel(int $tenantId, string $roleSlug): ?string
    {
        $setting = self::where('tenant_id', $tenantId)
            ->where('role_slug', $roleSlug)
            ->first();

        return $setting?->custom_label;
    }

    /**
     * Get the display label for a role (custom if set, otherwise default).
     */
    public static function getDisplayLabel(int $tenantId, string $roleSlug, string $defaultLabel): string
    {
        return self::getCustomLabel($tenantId, $roleSlug) ?? $defaultLabel;
    }

    /**
     * Set or update custom label for a role in a tenant.
     */
    public static function setCustomLabel(int $tenantId, string $roleSlug, string $customLabel): self
    {
        return self::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'role_slug' => $roleSlug,
            ],
            [
                'custom_label' => $customLabel,
            ]
        );
    }

    /**
     * Remove custom label for a role (revert to default).
     */
    public static function removeCustomLabel(int $tenantId, string $roleSlug): bool
    {
        return self::where('tenant_id', $tenantId)
            ->where('role_slug', $roleSlug)
            ->delete() > 0;
    }
}

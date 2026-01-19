<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * Log an audit event.
     */
    public function log(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $tags = null
    ): AuditLog {
        $user = Auth::user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'tenant_id' => $user?->tenant_id ?? ($model?->tenant_id ?? null),
            'event' => $action,
            'auditable_type' => $model ? get_class($model) : null,
            'auditable_id' => $model?->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => Request::fullUrl(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'tags' => $tags,
        ]);
    }

    /**
     * Get activity log for a user.
     */
    public function getActivityLog(int $userId, int $days = 30): Collection
    {
        return AuditLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get history for a specific model.
     */
    public function getModelHistory(string $modelType, int $modelId): Collection
    {
        return AuditLog::where('auditable_type', $modelType)
            ->where('auditable_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Log user login.
     */
    public function logLogin(User $user): AuditLog
    {
        return $this->log('user.login', $user, null, null, ['auth']);
    }

    /**
     * Log user logout.
     */
    public function logLogout(User $user): AuditLog
    {
        return $this->log('user.logout', $user, null, null, ['auth']);
    }

    /**
     * Log payment processing.
     */
    public function logPayment(Model $payment, array $details = []): AuditLog
    {
        return $this->log('payment.processed', $payment, null, $details, ['payment', 'financial']);
    }

    /**
     * Log invoice generation.
     */
    public function logInvoiceGeneration(Model $invoice): AuditLog
    {
        $amount = $invoice->getAttribute('total_amount') ?? $invoice->getAttribute('amount');
        return $this->log('invoice.generated', $invoice, null, [
            'invoice_id' => $invoice->getKey(),
            'amount' => $amount,
        ], ['invoice', 'financial']);
    }

    /**
     * Log user account changes.
     */
    public function logUserChange(User $user, array $oldValues, array $newValues): AuditLog
    {
        return $this->log('user.updated', $user, $oldValues, $newValues, ['user', 'account']);
    }

    /**
     * Log network user modifications.
     */
    public function logNetworkUserChange(Model $networkUser, array $oldValues, array $newValues): AuditLog
    {
        return $this->log('network_user.updated', $networkUser, $oldValues, $newValues, ['network', 'user']);
    }

    /**
     * Log model creation.
     */
    public function logCreated(Model $model, array $attributes = []): AuditLog
    {
        $modelName = class_basename($model);
        return $this->log("{$modelName}.created", $model, null, $attributes, ['created']);
    }

    /**
     * Log model update.
     */
    public function logUpdated(Model $model, array $oldValues, array $newValues): AuditLog
    {
        $modelName = class_basename($model);
        return $this->log("{$modelName}.updated", $model, $oldValues, $newValues, ['updated']);
    }

    /**
     * Log model deletion.
     */
    public function logDeleted(Model $model, array $attributes = []): AuditLog
    {
        $modelName = class_basename($model);
        return $this->log("{$modelName}.deleted", $model, $attributes, null, ['deleted']);
    }

    /**
     * Get recent activity across all users (for admins).
     */
    public function getRecentActivity(int $limit = 50, ?int $tenantId = null): Collection
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get();
    }

    /**
     * Get activity by event type.
     */
    public function getActivityByEvent(string $event, int $days = 30): Collection
    {
        return AuditLog::where('event', $event)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get activity by tags.
     */
    public function getActivityByTag(string $tag, int $days = 30): Collection
    {
        return AuditLog::whereJsonContains('tags', $tag)
            ->where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

<?php

namespace App\Traits;

use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Model;

trait HasAuditLog
{
    /**
     * Boot the trait.
     */
    protected static function bootHasAuditLog(): void
    {
        static::created(function (Model $model) {
            app(AuditLogService::class)->logCreated($model, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = array_diff_assoc($model->getRawOriginal(), $model->getAttributes());
            $newValues = array_intersect_key($model->getAttributes(), $oldValues);
            
            if (!empty($oldValues)) {
                app(AuditLogService::class)->logUpdated($model, $oldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            app(AuditLogService::class)->logDeleted($model, $model->getAttributes());
        });
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs()
    {
        return $this->morphMany(\App\Models\AuditLog::class, 'auditable');
    }
}

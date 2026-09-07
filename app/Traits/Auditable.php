<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            static::recordAuditLog($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $old = array_intersect_key($model->getOriginal(), $model->getDirty());
            $new = $model->getDirty();
            if (!empty($new)) {
                static::recordAuditLog($model, 'updated', $old, $new);
            }
        });

        static::deleted(function ($model) {
            static::recordAuditLog($model, 'deleted', $model->getOriginal(), null);
        });
    }

    protected static function recordAuditLog($model, string $action, ?array $old, ?array $new): void
    {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $old,
                'new_values' => $new,
                'url' => Request::fullUrl(),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);
        } catch (\Throwable $e) {
            // Silently skip if database or audit table not ready yet during migration/tests
        }
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }
}

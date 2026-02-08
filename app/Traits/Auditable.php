<?php

namespace App\Traits;

// app/Traits/Auditable.php
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            self::logAudit('created', $model);
        });

        static::updated(function ($model) {
            self::logAudit('updated', $model);
        });

        static::deleted(function ($model) {
            self::logAudit('deleted', $model);
        });
    }

    protected static function logAudit(string $action, $model): void
    {
        if (auth()->check()) {
            \App\Models\AuditLog::create([
                'action' => $action,
                'details' => json_encode([
                    'model' => get_class($model),
                    'id' => $model->id,
                    'changes' => $model->getChanges(),
                ]),
                'user_id' => auth()->id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }
    }
}
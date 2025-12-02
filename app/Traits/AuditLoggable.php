<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait AuditLoggable
{
    protected static function bootAuditLoggable(): void
    {
        static::created(function ($model) {
            $model->logAction('created', $model);
        });

        static::updated(function ($model) {
            $model->logAction('updated', $model);
        });

        static::deleted(function ($model) {
            $model->logAction('deleted', $model);
        });
    }

    protected function logAction(string $action, $model): void
    {
        // Skip logging if model has dontLog property
        if (property_exists($model, 'dontLog') && $model->dontLog) {
            return;
        }

        // Get changed attributes for updates
        $oldData = null;
        $newData = null;
        
        if ($action === 'updated') {
            $oldData = [];
            $newData = [];
            
            foreach ($model->getDirty() as $attribute => $newValue) {
                $oldData[$attribute] = $model->getOriginal($attribute);
                $newData[$attribute] = $newValue;
            }
            
            // Don't log if nothing changed
            if (empty($oldData)) {
                return;
            }
        } elseif ($action === 'created') {
            $newData = $model->getAttributes();
        } elseif ($action === 'deleted') {
            $oldData = $model->getOriginal();
        }

        // Determine entity type
        $entityType = class_basename($model);
        
        // Get user ID (could be null for system actions)
        $userId = Auth::id();
        
        AuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $model->id,
            'old_data' => $oldData,
            'new_data' => $newData,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
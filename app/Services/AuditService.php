<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function record(string $action, ?Model $subject = null, array $metadata = [], ?int $actorId = null): void
    {
        // Call inside the business transaction so a required audit write fails closed.
        AuditEvent::create([
            'actor_id' => $actorId ?? auth()->id(), 'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(), 'metadata' => $metadata, 'created_at' => now(),
        ]);
    }
}

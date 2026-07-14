<?php

declare(strict_types=1);

namespace App\Platform;

use App\Audit\AuditRecorder;
use App\Models\PlatformSupportNote;
use App\Models\Tenant;
use App\Models\User;

/**
 * Creates append-oriented platform support notes about a tenant. Notes are platform-only and
 * MUST NOT contain customer/medical data or secrets; creation is audited (rule 31 §10.9).
 */
final class SupportNoteService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    public function create(Tenant $tenant, User $author, string $body): PlatformSupportNote
    {
        $note = PlatformSupportNote::create([
            'tenant_id' => $tenant->id,
            'author_id' => $author->id,
            'body' => $body,
            'created_at' => now(),
        ]);

        $this->audit->record('platform.support-note.created', [
            'tenant_id' => $tenant->id,
            'actor_id' => (int) $author->id,
            'subject' => $note,
            'metadata' => ['tenant_id' => (int) $tenant->id],
        ]);

        return $note;
    }
}

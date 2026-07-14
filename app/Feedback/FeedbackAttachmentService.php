<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackAttachmentState;
use App\Enums\FeedbackEventType;
use App\Feedback\Exceptions\AttachmentRejectedException;
use App\Models\FeedbackAttachment;
use App\Models\FeedbackItem;
use App\Models\User;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores and removes internal operational attachments. Files are validated by CONTENT (detected MIME
 * from the file bytes, not the client-supplied name/type), written to a PRIVATE disk under a
 * tenant-prefixed path with a random stored filename, and recorded with a SHA-256 checksum. No public
 * path is ever produced. Removal is a state change, never a hard delete. No malware-scan state is
 * claimed because no scanner is wired in Step 8 (rule 33; Step 8 §14).
 */
final class FeedbackAttachmentService
{
    private const DISK = 'local';

    /** 10 MiB hard cap. */
    public const MAX_BYTES = 10_485_760;

    /** Detected-MIME allowlist mapped to a safe stored extension. */
    private const ALLOWED_MIME = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(
        private readonly FeedbackTimeline $timeline,
        private readonly AuditRecorder $audit,
        private readonly UsageMeter $usage,
    ) {}

    public function upload(FeedbackItem $item, UploadedFile $file, User $uploader): FeedbackAttachment
    {
        $size = $file->getSize();
        if ($size === false || $size === 0) {
            $this->auditRejection($item, $uploader, 'empty');
            throw AttachmentRejectedException::empty();
        }
        if ($size > self::MAX_BYTES) {
            $this->auditRejection($item, $uploader, 'too_large');
            throw AttachmentRejectedException::tooLarge();
        }

        // Content-based MIME detection (finfo/magic bytes) — never the client-supplied type.
        $detected = (string) $file->getMimeType();
        if (! array_key_exists($detected, self::ALLOWED_MIME)) {
            $this->auditRejection($item, $uploader, 'mime_not_allowed', $detected);
            throw AttachmentRejectedException::mimeNotAllowed($detected);
        }

        $extension = self::ALLOWED_MIME[$detected];
        $storedName = (string) Str::ulid().'.'.$extension;
        $directory = 'tenants/'.$item->tenant_id.'/feedback/'.$item->id.'/attachments';
        $checksum = hash_file('sha256', $file->getRealPath());
        $path = $file->storeAs($directory, $storedName, self::DISK);

        $attachment = FeedbackAttachment::create([
            'feedback_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'uploaded_by' => $uploader->id,
            'disk' => self::DISK,
            'path' => $path,
            'original_filename' => $this->sanitizeFilename($file->getClientOriginalName()),
            'stored_filename' => $storedName,
            'mime_type' => $detected,
            'size_bytes' => $size,
            'checksum_sha256' => $checksum !== false ? $checksum : '',
            'state' => FeedbackAttachmentState::Available,
        ]);

        $item->forceFill(['last_activity_at' => now()])->save();

        $this->usage->record(
            $item->tenant,
            MeterKeys::FEEDBACK_ATTACHMENTS_UPLOADED_BYTES,
            $size,
            'feedback-attachment:'.$attachment->ulid,
            actorId: $uploader->id,
        );

        $this->timeline->record($item, FeedbackEventType::AttachmentAdded, [
            'attachment_ulid' => $attachment->ulid,
            'mime_type' => $detected,
            'size_bytes' => $size,
        ], actorId: $uploader->id);

        $this->audit->record('feedback.attachment.added', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $uploader->id,
            'subject' => $item,
            'metadata' => ['attachment_ulid' => $attachment->ulid, 'mime_type' => $detected, 'size_bytes' => $size],
        ]);

        return $attachment;
    }

    public function remove(FeedbackAttachment $attachment, User $actor): FeedbackAttachment
    {
        $attachment->state = FeedbackAttachmentState::Removed;
        $attachment->removed_at = now();
        $attachment->save();

        if ($attachment->path !== '' && Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $this->timeline->record($attachment->item, FeedbackEventType::AttachmentRemoved, [
            'attachment_ulid' => $attachment->ulid,
        ], actorId: $actor->id);

        $this->audit->record('feedback.attachment.removed', [
            'tenant_id' => $attachment->tenant_id,
            'branch_id' => $attachment->branch_id,
            'actor_id' => $actor->id,
            'subject' => $attachment->item,
            'metadata' => ['attachment_ulid' => $attachment->ulid],
        ]);

        return $attachment;
    }

    private function auditRejection(FeedbackItem $item, User $uploader, string $reason, ?string $detected = null): void
    {
        $this->audit->record('feedback.attachment.rejected', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $uploader->id,
            'subject' => $item,
            'metadata' => array_filter(['reason' => $reason, 'detected_mime' => $detected]),
        ]);
    }

    private function sanitizeFilename(string $name): string
    {
        // Keep only the base name, strip control characters and path separators, and bound the length.
        $base = basename(str_replace(['\\', "\0"], '/', $name));
        $base = preg_replace('/[\x00-\x1F\x7F]/', '', $base) ?? 'file';

        return Str::limit(trim($base) !== '' ? $base : 'file', 180, '');
    }
}

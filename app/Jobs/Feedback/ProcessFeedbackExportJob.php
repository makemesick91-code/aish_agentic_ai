<?php

declare(strict_types=1);

namespace App\Jobs\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackExportStatus;
use App\Feedback\Export\FeedbackExportWriter;
use App\Models\FeedbackExport;
use App\Models\User;
use App\Notifications\NotificationType;
use App\Services\Notifications\NotificationDispatcher;
use App\Tenancy\Queue\TenantAware;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generates a requested feedback export on the queue, within the requester's rehydrated tenant
 * context. It is idempotent (a terminal export is a no-op), sets a truthful state (`Ready` only after
 * the private file is written, else `Failed` with a sanitized code), sets an expiry, and notifies the
 * requester. The file expiry and private disk mean the export is never publicly reachable
 * (rule 33; Step 8 §18).
 */
final class ProcessFeedbackExportJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;
    use TenantAware;

    public int $tries = 3;

    /** Days a ready export remains downloadable before it expires. */
    private const EXPIRY_DAYS = 7;

    public function __construct(public readonly int $exportId)
    {
        $this->captureTenantContext();
    }

    public function handle(FeedbackExportWriter $writer, NotificationDispatcher $dispatcher, AuditRecorder $audit): void
    {
        $export = FeedbackExport::find($this->exportId);
        if ($export === null || $export->status->isTerminal()) {
            return;
        }

        $export->update(['status' => FeedbackExportStatus::Processing]);

        try {
            $result = $writer->write($export);

            $export->update([
                'status' => FeedbackExportStatus::Ready,
                'disk' => 'local',
                'path' => $result->path,
                'row_count' => $result->rows,
                'size_bytes' => $result->sizeBytes,
                'ready_at' => now(),
                'expires_at' => now()->addDays(self::EXPIRY_DAYS),
            ]);

            $audit->record('feedback.export.ready', [
                'tenant_id' => $export->tenant_id,
                'subject' => $export,
                'metadata' => ['export_ulid' => $export->ulid, 'rows' => $result->rows],
            ]);

            $this->notify($dispatcher, $export, NotificationType::FeedbackExportReady, 'Your feedback export is ready');
        } catch (\Throwable $e) {
            Log::withContext(['tenant_id' => $export->tenant_id])
                ->error('Feedback export generation failed', ['export_ulid' => $export->ulid]);

            $export->update([
                'status' => FeedbackExportStatus::Failed,
                'failure_code' => 'generation_failed',
            ]);

            $audit->record('feedback.export.failed', [
                'tenant_id' => $export->tenant_id,
                'subject' => $export,
                'metadata' => ['export_ulid' => $export->ulid, 'failure_code' => 'generation_failed'],
            ]);

            $this->notify($dispatcher, $export, NotificationType::FeedbackExportFailed, 'Your feedback export could not be generated');
        }
    }

    private function notify(NotificationDispatcher $dispatcher, FeedbackExport $export, NotificationType $type, string $subject): void
    {
        if ($export->requested_by === null) {
            return;
        }

        $recipient = User::find($export->requested_by);
        if ($recipient === null) {
            return;
        }

        try {
            $dispatcher->dispatch(
                $type,
                $recipient,
                $type->value.':'.$export->ulid,
                $subject,
                tenant: $export->tenant,
                body: 'Feedback export '.$export->ulid.'.',
                data: ['export_ulid' => $export->ulid, 'status' => $export->status->value],
            );
        } catch (\Throwable) {
            // A notification failure must never fail the export job.
        }
    }
}

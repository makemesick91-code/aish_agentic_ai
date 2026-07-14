<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Enums\FeedbackAttachmentState;
use App\Enums\FeedbackEventType;
use App\Feedback\Exceptions\AttachmentRejectedException;
use App\Feedback\FeedbackAttachmentService;
use App\Models\FeedbackItem;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class FeedbackAttachmentTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    private Tenant $tenant;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->tenant = Tenant::factory()->create();
        $this->establishTenantContext($this->tenant);
        $this->actor = User::factory()->create();
    }

    private function service(): FeedbackAttachmentService
    {
        return app(FeedbackAttachmentService::class);
    }

    /** A real 1x1 PNG (valid magic bytes) so content-based MIME detection works without GD. */
    private function png(string $name): UploadedFile
    {
        $bytes = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
            true
        );

        return UploadedFile::fake()->createWithContent($name, $bytes !== false ? $bytes : '');
    }

    public function test_valid_image_is_stored_privately_with_metadata(): void
    {
        $item = FeedbackItem::factory()->create();
        $file = $this->png('evidence.png');

        $attachment = $this->service()->upload($item, $file, $this->actor);

        $this->assertSame(FeedbackAttachmentState::Available, $attachment->state);
        $this->assertSame('local', $attachment->disk);
        $this->assertStringStartsWith('tenants/'.$this->tenant->id.'/feedback/'.$item->id, $attachment->path);
        $this->assertNotSame('evidence.png', $attachment->stored_filename);
        $this->assertSame('evidence.png', $attachment->original_filename);
        $this->assertSame(64, strlen($attachment->checksum_sha256));
        Storage::disk('local')->assertExists($attachment->path);

        // Storage internals never serialize.
        $this->assertArrayNotHasKey('path', $attachment->toArray());
        $this->assertArrayNotHasKey('disk', $attachment->toArray());

        $this->assertDatabaseHas('feedback_events', ['feedback_item_id' => $item->id, 'type' => FeedbackEventType::AttachmentAdded->value]);
        $this->assertDatabaseHas('usage_records', ['tenant_id' => $this->tenant->id, 'meter_key' => 'feedback_attachments.uploaded_bytes']);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.attachment.added']);
    }

    public function test_pdf_content_is_accepted(): void
    {
        $item = FeedbackItem::factory()->create();
        $pdf = UploadedFile::fake()->createWithContent('doc.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF\n");

        $attachment = $this->service()->upload($item, $pdf, $this->actor);

        $this->assertSame('application/pdf', $attachment->mime_type);
    }

    /**
     * The service validates the CONTENT-detected MIME (finfo) in production. The test harness's fake
     * files derive their MIME from the extension, so this exercises the reject path (audited, no row
     * stored) for a disallowed type; the production finfo sniffing is asserted in the boundary test
     * (the service must call getMimeType(), never getClientMimeType()).
     */
    public function test_disallowed_html_type_is_rejected_and_audited(): void
    {
        $item = FeedbackItem::factory()->create();
        $file = UploadedFile::fake()->createWithContent('malicious.html', '<html><script>alert(1)</script></html>');

        try {
            $this->service()->upload($item, $file, $this->actor);
            $this->fail('Expected AttachmentRejectedException.');
        } catch (AttachmentRejectedException $e) {
            $this->assertSame('mime_not_allowed', $e->reasonCode);
        }
        $this->assertDatabaseCount('feedback_attachments', 0);
        $this->assertDatabaseHas('audit_logs', ['event' => 'feedback.attachment.rejected']);
    }

    public function test_svg_is_rejected(): void
    {
        $item = FeedbackItem::factory()->create();
        $file = UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

        $this->expectException(AttachmentRejectedException::class);
        $this->service()->upload($item, $file, $this->actor);
    }

    public function test_oversize_file_is_rejected(): void
    {
        $item = FeedbackItem::factory()->create();
        $file = UploadedFile::fake()->create('big.pdf', 11_000); // ~11 MB

        $this->expectException(AttachmentRejectedException::class);
        $this->service()->upload($item, $file, $this->actor);
    }

    public function test_traversal_filename_is_sanitized(): void
    {
        $item = FeedbackItem::factory()->create();
        $upload = $this->png('../../../../etc/passwd.png');

        $attachment = $this->service()->upload($item, $upload, $this->actor);

        $this->assertStringNotContainsString('..', $attachment->original_filename);
        $this->assertStringNotContainsString('/', $attachment->original_filename);
    }

    public function test_remove_sets_state_and_deletes_file(): void
    {
        $item = FeedbackItem::factory()->create();
        $attachment = $this->service()->upload($item, $this->png('a.png'), $this->actor);
        $path = $attachment->path;

        $this->service()->remove($attachment, $this->actor);

        $this->assertSame(FeedbackAttachmentState::Removed, $attachment->fresh()->state);
        Storage::disk('local')->assertMissing($path);
        $this->assertDatabaseHas('feedback_events', ['feedback_item_id' => $item->id, 'type' => FeedbackEventType::AttachmentRemoved->value]);
    }

    public function test_attachment_cannot_be_hard_deleted(): void
    {
        $item = FeedbackItem::factory()->create();
        $attachment = $this->service()->upload($item, $this->png('a.png'), $this->actor);

        $this->expectException(\RuntimeException::class);
        $attachment->delete();
    }
}

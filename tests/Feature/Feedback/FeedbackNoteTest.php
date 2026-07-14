<?php

declare(strict_types=1);

namespace Tests\Feature\Feedback;

use App\Enums\FeedbackEventType;
use App\Feedback\FeedbackNoteService;
use App\Models\FeedbackItem;
use App\Models\FeedbackNote;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\InteractsWithTenancy;
use Tests\TestCase;

final class FeedbackNoteTest extends TestCase
{
    use InteractsWithTenancy;
    use RefreshDatabase;

    private User $actor;

    protected function setUp(): void
    {
        parent::setUp();
        $tenant = Tenant::factory()->create();
        $this->establishTenantContext($tenant);
        $this->actor = User::factory()->create();
    }

    private function service(): FeedbackNoteService
    {
        return app(FeedbackNoteService::class);
    }

    public function test_note_is_created_and_body_never_leaks_to_timeline_or_audit(): void
    {
        $item = FeedbackItem::factory()->create();
        $secret = 'SENSITIVE-NOTE-BODY-'.uniqid();

        $note = $this->service()->addNote($item, $this->actor, $secret);

        $this->assertDatabaseHas('feedback_notes', ['id' => $note->id, 'author_id' => $this->actor->id]);
        $this->assertDatabaseHas('feedback_events', ['feedback_item_id' => $item->id, 'type' => FeedbackEventType::NoteCreated->value]);

        // The body must not appear in any timeline event metadata nor audit metadata.
        $eventMeta = (string) DB::table('feedback_events')->where('feedback_item_id', $item->id)->pluck('metadata')->implode(' ');
        $auditMeta = (string) DB::table('audit_logs')->where('event', 'feedback.note.created')->pluck('metadata')->implode(' ');
        $this->assertStringNotContainsString($secret, $eventMeta);
        $this->assertStringNotContainsString($secret, $auditMeta);
    }

    public function test_empty_note_is_rejected(): void
    {
        $item = FeedbackItem::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->service()->addNote($item, $this->actor, '   ');
    }

    public function test_overlong_note_is_rejected(): void
    {
        $item = FeedbackItem::factory()->create();

        $this->expectException(\RuntimeException::class);
        $this->service()->addNote($item, $this->actor, str_repeat('a', FeedbackNote::MAX_BODY_LENGTH + 1));
    }

    public function test_notes_are_append_only(): void
    {
        $item = FeedbackItem::factory()->create();
        $note = $this->service()->addNote($item, $this->actor, 'original');

        $this->expectException(\RuntimeException::class);
        $note->update(['body' => 'tampered']);
    }
}

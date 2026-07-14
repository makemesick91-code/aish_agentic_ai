<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedbackAttachmentState;
use App\Models\FeedbackAttachment;
use App\Models\FeedbackItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<FeedbackAttachment>
 */
class FeedbackAttachmentFactory extends Factory
{
    protected $model = FeedbackAttachment::class;

    public function definition(): array
    {
        $item = FeedbackItem::factory()->create();
        $stored = Str::ulid().'.pdf';

        return [
            'tenant_id' => $item->tenant_id,
            'feedback_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'uploaded_by' => User::factory(),
            'disk' => 'local',
            'path' => 'tenants/'.$item->tenant_id.'/feedback/'.$item->id.'/'.$stored,
            'original_filename' => 'evidence.pdf',
            'stored_filename' => $stored,
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', (string) Str::ulid()),
            'state' => FeedbackAttachmentState::Available,
            'rejected_reason' => null,
        ];
    }
}

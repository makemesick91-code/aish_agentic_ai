<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FeedbackEventType;
use App\Models\FeedbackEvent;
use App\Models\FeedbackItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackEvent>
 */
class FeedbackEventFactory extends Factory
{
    protected $model = FeedbackEvent::class;

    public function definition(): array
    {
        $item = FeedbackItem::factory()->create();

        return [
            'tenant_id' => $item->tenant_id,
            'feedback_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'type' => FeedbackEventType::Projected,
            'actor_id' => null,
            'metadata' => [],
        ];
    }
}

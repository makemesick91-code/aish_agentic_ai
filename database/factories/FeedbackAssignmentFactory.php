<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeedbackAssignment;
use App\Models\FeedbackItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackAssignment>
 */
class FeedbackAssignmentFactory extends Factory
{
    protected $model = FeedbackAssignment::class;

    public function definition(): array
    {
        $item = FeedbackItem::factory()->create();

        return [
            'tenant_id' => $item->tenant_id,
            'feedback_item_id' => $item->id,
            'previous_assignee_id' => null,
            'new_assignee_id' => null,
            'actor_id' => null,
            'reason' => null,
        ];
    }
}

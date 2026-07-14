<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\FeedbackItem;
use App\Models\FeedbackNote;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeedbackNote>
 */
class FeedbackNoteFactory extends Factory
{
    protected $model = FeedbackNote::class;

    public function definition(): array
    {
        $item = FeedbackItem::factory()->create();

        return [
            'tenant_id' => $item->tenant_id,
            'feedback_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'author_id' => User::factory(),
            'body' => fake()->sentence(),
        ];
    }
}

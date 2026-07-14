<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlatformSupportNote;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformSupportNote>
 */
class PlatformSupportNoteFactory extends Factory
{
    protected $model = PlatformSupportNote::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'author_id' => User::factory(),
            'body' => fake()->sentence(),
            'created_at' => now(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantSetting>
 */
class TenantSettingFactory extends Factory
{
    protected $model = TenantSetting::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'timezone' => 'Asia/Makassar',
            'locale' => 'en',
            'data_retention_days' => 365,
            'invitation_expiry_days' => 7,
            'require_email_verification' => true,
        ];
    }
}

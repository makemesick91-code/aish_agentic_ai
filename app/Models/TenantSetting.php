<?php

declare(strict_types=1);

namespace App\Models;

use App\Tenancy\Concerns\BelongsToTenant;
use App\Tenancy\Contracts\TenantOwned;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model implements TenantOwned
{
    use BelongsToTenant;

    /** @use HasFactory<Factory<self>> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'timezone',
        'locale',
        'data_retention_days',
        'invitation_expiry_days',
        'require_email_verification',
    ];

    protected function casts(): array
    {
        return [
            'data_retention_days' => 'integer',
            'invitation_expiry_days' => 'integer',
            'require_email_verification' => 'boolean',
        ];
    }
}

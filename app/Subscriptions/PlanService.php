<?php

declare(strict_types=1);

namespace App\Subscriptions;

use App\Audit\AuditRecorder;
use App\Enums\FeatureType;
use App\Enums\PlanStatus;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Subscriptions\Exceptions\InvalidEntitlementException;
use Illuminate\Support\Facades\DB;

/**
 * Platform-side plan catalog management: create, update, activate, retire, and define typed
 * entitlements. Every change is audited. Plan versions must not silently change historical
 * meaning — a materially different plan is a new version, not an edit (rule 31 §9.2, §9.3).
 */
final class PlanService
{
    public function __construct(private readonly AuditRecorder $audit) {}

    /**
     * @param  array{code: string, version?: int, name: string, description?: ?string, public_visible?: bool}  $data
     */
    public function create(array $data, ?int $actorId = null): Plan
    {
        $plan = Plan::create([
            'code' => $data['code'],
            'version' => $data['version'] ?? 1,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => PlanStatus::Draft,
            'public_visible' => $data['public_visible'] ?? false,
        ]);

        $this->audit->record('plan.created', [
            'tenant_id' => null,
            'actor_id' => $actorId,
            'subject' => $plan,
            'metadata' => ['code' => $plan->code, 'version' => $plan->version],
        ]);

        return $plan;
    }

    /**
     * @param  array{name?: string, description?: ?string, public_visible?: bool}  $data
     */
    public function update(Plan $plan, array $data, ?int $actorId = null): Plan
    {
        // code and version are immutable — a different meaning is a new version.
        $plan->fill(array_filter([
            'name' => $data['name'] ?? null,
            'description' => $data['description'] ?? null,
        ], static fn ($value): bool => $value !== null));

        if (array_key_exists('public_visible', $data)) {
            $plan->public_visible = $data['public_visible'];
        }

        $plan->save();

        $this->audit->record('plan.updated', [
            'tenant_id' => null,
            'actor_id' => $actorId,
            'subject' => $plan,
            'metadata' => ['code' => $plan->code, 'version' => $plan->version],
        ]);

        return $plan;
    }

    public function activate(Plan $plan, ?int $actorId = null): Plan
    {
        $plan->forceFill([
            'status' => PlanStatus::Active,
            'effective_from' => $plan->effective_from ?? now(),
        ])->save();

        $this->audit->record('plan.activated', [
            'tenant_id' => null,
            'actor_id' => $actorId,
            'subject' => $plan,
            'metadata' => ['code' => $plan->code, 'version' => $plan->version],
        ]);

        return $plan;
    }

    public function retire(Plan $plan, ?int $actorId = null): Plan
    {
        $plan->forceFill([
            'status' => PlanStatus::Retired,
            'effective_to' => now(),
        ])->save();

        $this->audit->record('plan.retired', [
            'tenant_id' => null,
            'actor_id' => $actorId,
            'subject' => $plan,
            'metadata' => ['code' => $plan->code, 'version' => $plan->version],
        ]);

        return $plan;
    }

    public function setFeature(Plan $plan, string $key, bool|int|string $value, ?int $actorId = null): PlanFeature
    {
        $type = EntitlementKeys::typeFor($key);

        if ($type === null) {
            throw InvalidEntitlementException::unknownKey($key);
        }

        $this->assertValueMatchesType($key, $type, $value);

        return DB::transaction(function () use ($plan, $key, $type, $value, $actorId): PlanFeature {
            $feature = PlanFeature::updateOrCreate(
                ['plan_id' => $plan->id, 'key' => $key],
                [
                    'type' => $type,
                    'value_boolean' => $type === FeatureType::Boolean ? (bool) $value : null,
                    'value_int' => $type === FeatureType::Integer ? (int) $value : null,
                    'value_string' => $type === FeatureType::StringValue ? (string) $value : null,
                ],
            );

            $this->audit->record('plan.entitlement.changed', [
                'tenant_id' => null,
                'actor_id' => $actorId,
                'subject' => $plan,
                'metadata' => ['key' => $key, 'type' => $type->value],
            ]);

            return $feature;
        });
    }

    private function assertValueMatchesType(string $key, FeatureType $type, bool|int|string $value): void
    {
        match ($type) {
            FeatureType::Boolean => is_bool($value) ? null : throw InvalidEntitlementException::typeMismatch($key, 'boolean'),
            FeatureType::Integer => $this->assertInteger($key, $value),
            FeatureType::StringValue => is_string($value) ? null : throw InvalidEntitlementException::typeMismatch($key, 'string'),
        };
    }

    private function assertInteger(string $key, bool|int|string $value): void
    {
        if (! is_int($value)) {
            throw InvalidEntitlementException::typeMismatch($key, 'integer');
        }

        if ($value < 0 && $value !== EntitlementKeys::UNLIMITED) {
            throw InvalidEntitlementException::negativeLimit($key);
        }
    }
}

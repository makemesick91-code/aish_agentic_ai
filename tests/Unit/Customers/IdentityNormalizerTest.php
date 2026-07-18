<?php

declare(strict_types=1);

namespace Tests\Unit\Customers;

use App\Customers\Exceptions\InvalidIdentityValueException;
use App\Customers\Identity\IdentityNormalizer;
use App\Enums\CustomerIdentityType;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Identity normalization is the foundation of duplicate prevention AND the main identity-poisoning
 * surface: normalizing too aggressively silently merges two real people (rule 36; ADR 0071).
 */
final class IdentityNormalizerTest extends TestCase
{
    private IdentityNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new IdentityNormalizer;
    }

    public function test_email_is_lowercased_and_trimmed(): void
    {
        $result = $this->normalizer->normalize(CustomerIdentityType::Email, '  Ana.Lopez@Example.COM ');

        $this->assertSame('ana.lopez@example.com', $result->value);
        $this->assertSame(IdentityNormalizer::VERSION, $result->normalizerVersion);
    }

    /**
     * The critical anti-poisoning guarantee: dots and +tags are NOT stripped, because those rules
     * are provider-specific and a wrong collapse merges distinct mailboxes irreversibly.
     */
    public function test_email_local_part_is_preserved_verbatim(): void
    {
        $dotted = $this->normalizer->normalize(CustomerIdentityType::Email, 'a.n.a@example.com');
        $plain = $this->normalizer->normalize(CustomerIdentityType::Email, 'ana@example.com');
        $tagged = $this->normalizer->normalize(CustomerIdentityType::Email, 'ana+clinic@example.com');

        $this->assertNotSame($plain->value, $dotted->value);
        $this->assertNotSame($plain->value, $tagged->value);
        $this->assertSame('ana+clinic@example.com', $tagged->value);
    }

    public function test_normalization_is_idempotent(): void
    {
        $once = $this->normalizer->normalize(CustomerIdentityType::Email, 'Ana@Example.com')->value;
        $twice = $this->normalizer->normalize(CustomerIdentityType::Email, $once)->value;

        $this->assertSame($once, $twice);
    }

    #[DataProvider('invalidEmails')]
    public function test_invalid_email_is_refused(string $value): void
    {
        $this->expectException(InvalidIdentityValueException::class);

        $this->normalizer->normalize(CustomerIdentityType::Email, $value);
    }

    /** @return array<string, array{string}> */
    public static function invalidEmails(): array
    {
        return [
            'no at sign' => ['not-an-email'],
            'no domain dot' => ['ana@localhost'],
            'empty' => ['   '],
            'spaces inside' => ['an a@example.com'],
        ];
    }

    public function test_phone_in_international_form_is_normalized_to_e164(): void
    {
        $result = $this->normalizer->normalize(CustomerIdentityType::Phone, '+62 (811) 234-5678');

        $this->assertSame('+628112345678', $result->value);
    }

    public function test_double_zero_prefix_becomes_plus(): void
    {
        $result = $this->normalizer->normalize(CustomerIdentityType::Phone, '0062 811 2345678');

        $this->assertSame('+628112345678', $result->value);
    }

    public function test_national_number_uses_the_tenant_region_and_drops_the_trunk_zero(): void
    {
        $result = $this->normalizer->normalize(CustomerIdentityType::Phone, '0811 2345678', '62');

        $this->assertSame('+628112345678', $result->value);
    }

    /**
     * Without a region a national number is genuinely ambiguous — refusing it is what stops two
     * different people in different countries from colliding on one identity.
     */
    public function test_national_number_without_a_region_is_refused(): void
    {
        $this->expectException(InvalidIdentityValueException::class);

        $this->normalizer->normalize(CustomerIdentityType::Phone, '0811 2345678');
    }

    #[DataProvider('invalidPhones')]
    public function test_invalid_phone_is_refused(string $value): void
    {
        $this->expectException(InvalidIdentityValueException::class);

        $this->normalizer->normalize(CustomerIdentityType::Phone, $value, '62');
    }

    /** @return array<string, array{string}> */
    public static function invalidPhones(): array
    {
        return [
            'letters' => ['+62-CALL-NOW'],
            'too short' => ['+62123'],
            'too long' => ['+6281123456789012345'],
            'only zeroes' => ['000'],
            'empty' => ['  '],
        ];
    }

    public function test_external_ref_preserves_case_and_punctuation(): void
    {
        $result = $this->normalizer->normalize(CustomerIdentityType::ExternalRef, '  MRN-00A_b/12 ');

        $this->assertSame('MRN-00A_b/12', $result->value);
    }

    public function test_pii_types_never_expose_a_persistable_value(): void
    {
        $email = $this->normalizer->normalize(CustomerIdentityType::Email, 'ana@example.com');
        $phone = $this->normalizer->normalize(CustomerIdentityType::Phone, '+628112345678');
        $ref = $this->normalizer->normalize(CustomerIdentityType::ExternalRef, 'MRN-1');

        $this->assertNull($email->persistableValue());
        $this->assertNull($phone->persistableValue());
        $this->assertSame('MRN-1', $ref->persistableValue());
    }

    public function test_overlong_value_is_refused(): void
    {
        $this->expectException(InvalidIdentityValueException::class);

        $this->normalizer->normalize(
            CustomerIdentityType::Email,
            str_repeat('a', 200).'@example.com'
        );
    }
}

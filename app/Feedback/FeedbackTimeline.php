<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Enums\FeedbackEventType;
use App\Models\FeedbackEvent;
use App\Models\FeedbackItem;
use Illuminate\Support\Facades\Auth;

/**
 * The single writer of the append-only operational timeline. Metadata is allowlisted and sanitized:
 * only scalar/null values survive, sensitive keys are redacted, and objects are dropped — so a note
 * body, response free text, attachment content, token, or storage path can never reach a timeline
 * row (rule 33; Step 8 §15, §24).
 */
final class FeedbackTimeline
{
    /** Keys whose values are always redacted, defence-in-depth against accidental leakage. */
    private const REDACTED_KEYS = [
        'token', 'token_hash', 'password', 'secret', 'api_key', 'access_token',
        'refresh_token', 'body', 'note', 'content', 'answer', 'path', 'stored_filename',
    ];

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(FeedbackItem $item, FeedbackEventType $type, array $metadata = [], ?int $actorId = null): FeedbackEvent
    {
        return FeedbackEvent::create([
            'tenant_id' => $item->tenant_id,
            'feedback_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'type' => $type,
            'actor_id' => $actorId ?? Auth::id(),
            'metadata' => $this->sanitize($metadata),
        ]);
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    private function sanitize(array $metadata): array
    {
        $clean = [];
        foreach ($metadata as $key => $value) {
            if (in_array(strtolower((string) $key), self::REDACTED_KEYS, true)) {
                $clean[$key] = '[redacted]';

                continue;
            }
            if (is_array($value)) {
                $clean[$key] = $this->sanitize($value);
            } elseif (is_scalar($value) || $value === null) {
                $clean[$key] = $value;
            } else {
                $clean[$key] = '[omitted]';
            }
        }

        return $clean;
    }
}

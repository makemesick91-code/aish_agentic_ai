<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Feedback\Exceptions\EntitlementDeniedException;
use App\Feedback\FeedbackEntitlements;
use App\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates every feedback surface on the authoritative FEEDBACK_ENABLED entitlement (fail-closed). This
 * is the single capability guard for the module, consistent with the attachment/export/bulk sub-guards
 * (rule 33; Step 8 §22).
 */
final class EnsureFeedbackEnabled
{
    public function __construct(
        private readonly FeedbackEntitlements $entitlements,
        private readonly TenantContext $context,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            $this->entitlements->assertFeedbackEnabled($this->context->tenant());
        } catch (EntitlementDeniedException $e) {
            abort(403, $e->getMessage());
        }

        return $next($request);
    }
}

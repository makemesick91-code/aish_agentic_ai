<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;
use App\Policies\Concerns\ScopesToBranch;

class SurveyResponsePolicy
{
    use ChecksTenantScope;
    use ScopesToBranch;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SURVEY_RESPONSES_VIEW);
    }

    public function view(User $user, SurveyResponse $response): bool
    {
        return $user->can(Permissions::SURVEY_RESPONSES_VIEW) && $this->reachable($response);
    }

    public function invalidate(User $user, SurveyResponse $response): bool
    {
        return $user->can(Permissions::SURVEY_RESPONSES_INVALIDATE) && $this->reachable($response);
    }

    private function reachable(SurveyResponse $response): bool
    {
        return $this->inCurrentTenant($response) && $this->canAccessBranch($response->branch_id);
    }
}

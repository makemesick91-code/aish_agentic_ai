<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\Survey;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;
use App\Policies\Concerns\ScopesToBranch;

/**
 * Server-side authorization for surveys. Every mutating ability requires the specific
 * permission AND that the survey belongs to the current tenant AND that the actor may reach the
 * survey's branch. UI hiding is never sufficient (rule 30, rule 32; Step 7 §27).
 */
class SurveyPolicy
{
    use ChecksTenantScope;
    use ScopesToBranch;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SURVEYS_VIEW);
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->can(Permissions::SURVEYS_VIEW) && $this->reachable($survey);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::SURVEYS_CREATE);
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->can(Permissions::SURVEYS_UPDATE) && $this->reachable($survey);
    }

    public function publish(User $user, Survey $survey): bool
    {
        return $user->can(Permissions::SURVEYS_PUBLISH) && $this->reachable($survey);
    }

    public function pause(User $user, Survey $survey): bool
    {
        return $user->can(Permissions::SURVEYS_PAUSE) && $this->reachable($survey);
    }

    public function archive(User $user, Survey $survey): bool
    {
        return $user->can(Permissions::SURVEYS_ARCHIVE) && $this->reachable($survey);
    }

    public function preview(User $user, Survey $survey): bool
    {
        // Preview reveals draft content; it requires authoring-level view, never public access.
        return $user->can(Permissions::SURVEYS_VIEW) && $this->reachable($survey);
    }

    public function viewResults(User $user, Survey $survey): bool
    {
        return $user->can(Permissions::SURVEYS_RESULTS_VIEW) && $this->reachable($survey);
    }

    private function reachable(Survey $survey): bool
    {
        return $this->inCurrentTenant($survey) && $this->canAccessBranch($survey->branch_id);
    }
}

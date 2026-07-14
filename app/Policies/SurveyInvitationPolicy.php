<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\SurveyInvitation;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;
use App\Policies\Concerns\ScopesToBranch;

class SurveyInvitationPolicy
{
    use ChecksTenantScope;
    use ScopesToBranch;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SURVEY_INVITATIONS_VIEW);
    }

    public function view(User $user, SurveyInvitation $invitation): bool
    {
        return $user->can(Permissions::SURVEY_INVITATIONS_VIEW) && $this->reachable($invitation);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::SURVEY_INVITATIONS_CREATE);
    }

    public function revoke(User $user, SurveyInvitation $invitation): bool
    {
        return $user->can(Permissions::SURVEY_INVITATIONS_REVOKE) && $this->reachable($invitation);
    }

    private function reachable(SurveyInvitation $invitation): bool
    {
        return $this->inCurrentTenant($invitation) && $this->canAccessBranch($invitation->branch_id);
    }
}

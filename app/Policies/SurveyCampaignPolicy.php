<?php

declare(strict_types=1);

namespace App\Policies;

use App\Authorization\Permissions;
use App\Models\SurveyCampaign;
use App\Models\User;
use App\Policies\Concerns\ChecksTenantScope;
use App\Policies\Concerns\ScopesToBranch;

class SurveyCampaignPolicy
{
    use ChecksTenantScope;
    use ScopesToBranch;

    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_VIEW);
    }

    public function view(User $user, SurveyCampaign $campaign): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_VIEW) && $this->reachable($campaign);
    }

    public function create(User $user): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_CREATE);
    }

    public function update(User $user, SurveyCampaign $campaign): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_UPDATE) && $this->reachable($campaign);
    }

    public function activate(User $user, SurveyCampaign $campaign): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_ACTIVATE) && $this->reachable($campaign);
    }

    public function pause(User $user, SurveyCampaign $campaign): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_PAUSE) && $this->reachable($campaign);
    }

    public function end(User $user, SurveyCampaign $campaign): bool
    {
        return $user->can(Permissions::SURVEY_CAMPAIGNS_END) && $this->reachable($campaign);
    }

    private function reachable(SurveyCampaign $campaign): bool
    {
        return $this->inCurrentTenant($campaign) && $this->canAccessBranch($campaign->branch_id);
    }
}

<?php

declare(strict_types=1);

namespace App\Authorization;

/**
 * Stable, machine-readable foundation permission names. Business-module permissions are
 * added by their modules in later steps; this catalog is foundation-only (rule 30).
 */
final class Permissions
{
    public const TENANT_VIEW = 'tenant.view';

    public const TENANT_UPDATE = 'tenant.update';

    public const BRANCHES_VIEW = 'branches.view';

    public const BRANCHES_CREATE = 'branches.create';

    public const BRANCHES_UPDATE = 'branches.update';

    public const BRANCHES_DEACTIVATE = 'branches.deactivate';

    public const USERS_VIEW = 'users.view';

    public const USERS_INVITE = 'users.invite';

    public const USERS_MANAGE_MEMBERSHIPS = 'users.manage-memberships';

    public const ROLES_VIEW = 'roles.view';

    public const ROLES_ASSIGN = 'roles.assign';

    public const ROLES_MANAGE_FOUNDATION = 'roles.manage-foundation';

    public const AUDIT_VIEW = 'audit.view';

    public const CONTEXT_SWITCH_TENANT = 'context.switch-tenant';

    public const CONTEXT_SWITCH_BRANCH = 'context.switch-branch';

    // Step 7 — Survey & CSAT Foundation (rule 32).
    public const SURVEYS_VIEW = 'surveys.view';

    public const SURVEYS_CREATE = 'surveys.create';

    public const SURVEYS_UPDATE = 'surveys.update';

    public const SURVEYS_PUBLISH = 'surveys.publish';

    public const SURVEYS_PAUSE = 'surveys.pause';

    public const SURVEYS_ARCHIVE = 'surveys.archive';

    public const SURVEYS_RESULTS_VIEW = 'surveys.results.view';

    public const SURVEY_CAMPAIGNS_VIEW = 'survey-campaigns.view';

    public const SURVEY_CAMPAIGNS_CREATE = 'survey-campaigns.create';

    public const SURVEY_CAMPAIGNS_UPDATE = 'survey-campaigns.update';

    public const SURVEY_CAMPAIGNS_ACTIVATE = 'survey-campaigns.activate';

    public const SURVEY_CAMPAIGNS_PAUSE = 'survey-campaigns.pause';

    public const SURVEY_CAMPAIGNS_END = 'survey-campaigns.end';

    public const SURVEY_INVITATIONS_VIEW = 'survey-invitations.view';

    public const SURVEY_INVITATIONS_CREATE = 'survey-invitations.create';

    public const SURVEY_INVITATIONS_REVOKE = 'survey-invitations.revoke';

    public const SURVEY_RESPONSES_VIEW = 'survey-responses.view';

    public const SURVEY_RESPONSES_INVALIDATE = 'survey-responses.invalidate';

    /** @return list<string> */
    public static function surveyPermissions(): array
    {
        return [
            self::SURVEYS_VIEW,
            self::SURVEYS_CREATE,
            self::SURVEYS_UPDATE,
            self::SURVEYS_PUBLISH,
            self::SURVEYS_PAUSE,
            self::SURVEYS_ARCHIVE,
            self::SURVEYS_RESULTS_VIEW,
            self::SURVEY_CAMPAIGNS_VIEW,
            self::SURVEY_CAMPAIGNS_CREATE,
            self::SURVEY_CAMPAIGNS_UPDATE,
            self::SURVEY_CAMPAIGNS_ACTIVATE,
            self::SURVEY_CAMPAIGNS_PAUSE,
            self::SURVEY_CAMPAIGNS_END,
            self::SURVEY_INVITATIONS_VIEW,
            self::SURVEY_INVITATIONS_CREATE,
            self::SURVEY_INVITATIONS_REVOKE,
            self::SURVEY_RESPONSES_VIEW,
            self::SURVEY_RESPONSES_INVALIDATE,
        ];
    }

    /** Read-only survey visibility (surveys + results). */
    public const SURVEY_READ_ONLY = [
        self::SURVEYS_VIEW,
        self::SURVEYS_RESULTS_VIEW,
    ];

    /** Branch-scoped operational survey visibility. */
    public const SURVEY_BRANCH_VIEW = [
        self::SURVEYS_VIEW,
        self::SURVEYS_RESULTS_VIEW,
        self::SURVEY_CAMPAIGNS_VIEW,
        self::SURVEY_INVITATIONS_VIEW,
        self::SURVEY_RESPONSES_VIEW,
    ];

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::TENANT_VIEW,
            self::TENANT_UPDATE,
            self::BRANCHES_VIEW,
            self::BRANCHES_CREATE,
            self::BRANCHES_UPDATE,
            self::BRANCHES_DEACTIVATE,
            self::USERS_VIEW,
            self::USERS_INVITE,
            self::USERS_MANAGE_MEMBERSHIPS,
            self::ROLES_VIEW,
            self::ROLES_ASSIGN,
            self::ROLES_MANAGE_FOUNDATION,
            self::AUDIT_VIEW,
            self::CONTEXT_SWITCH_TENANT,
            self::CONTEXT_SWITCH_BRANCH,
            ...self::surveyPermissions(),
        ];
    }
}

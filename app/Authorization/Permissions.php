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

    // Step 8 — Feedback Operations Foundation (rule 33).
    public const FEEDBACK_VIEW = 'feedback.view';

    public const FEEDBACK_VIEW_CONTENT = 'feedback.view-content';

    public const FEEDBACK_MANAGE_STATUS = 'feedback.manage-status';

    public const FEEDBACK_ASSIGN = 'feedback.assign';

    public const FEEDBACK_TAGS_MANAGE = 'feedback.tags.manage';

    public const FEEDBACK_NOTES_CREATE = 'feedback.notes.create';

    public const FEEDBACK_ATTACHMENTS_MANAGE = 'feedback.attachments.manage';

    public const FEEDBACK_BULK_MANAGE = 'feedback.bulk-manage';

    public const FEEDBACK_EXPORT = 'feedback.export';

    public const FEEDBACK_SUMMARY_VIEW = 'feedback.summary.view';

    // Step 10 — Customer 360 Foundation (rule 36).
    public const CUSTOMER_VIEW = 'customer.view';

    /** Reading contact PII (email/phone) — deliberately separate from CUSTOMER_VIEW. */
    public const CUSTOMER_VIEW_CONTACT = 'customer.view-contact';

    public const CUSTOMER_MANAGE = 'customer.manage';

    /**
     * Merge and split. Separated from CUSTOMER_MANAGE because an incorrect merge is the
     * highest-blast-radius action in Customer 360 (rule 36; ADR 0072).
     */
    public const CUSTOMER_MERGE = 'customer.merge';

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

    /** @return list<string> */
    public static function feedbackPermissions(): array
    {
        return [
            self::FEEDBACK_VIEW,
            self::FEEDBACK_VIEW_CONTENT,
            self::FEEDBACK_MANAGE_STATUS,
            self::FEEDBACK_ASSIGN,
            self::FEEDBACK_TAGS_MANAGE,
            self::FEEDBACK_NOTES_CREATE,
            self::FEEDBACK_ATTACHMENTS_MANAGE,
            self::FEEDBACK_BULK_MANAGE,
            self::FEEDBACK_EXPORT,
            self::FEEDBACK_SUMMARY_VIEW,
        ];
    }

    /** @return list<string> */
    public static function customerPermissions(): array
    {
        return [
            self::CUSTOMER_VIEW,
            self::CUSTOMER_VIEW_CONTACT,
            self::CUSTOMER_MANAGE,
            self::CUSTOMER_MERGE,
        ];
    }

    /**
     * Branch-scoped operational customer work. Merge is intentionally EXCLUDED: a branch operator
     * must not reshape tenant-wide customer identity (rule 36; ADR 0072).
     */
    public const CUSTOMER_BRANCH_OPS = [
        self::CUSTOMER_VIEW,
        self::CUSTOMER_VIEW_CONTACT,
        self::CUSTOMER_MANAGE,
    ];

    /** Safe read-only customer visibility — metadata only, never contact PII. */
    public const CUSTOMER_READ_ONLY = [
        self::CUSTOMER_VIEW,
    ];

    /** Branch-scoped operational feedback work (no tenant-wide bulk actions). */
    public const FEEDBACK_BRANCH_OPS = [
        self::FEEDBACK_VIEW,
        self::FEEDBACK_VIEW_CONTENT,
        self::FEEDBACK_MANAGE_STATUS,
        self::FEEDBACK_ASSIGN,
        self::FEEDBACK_TAGS_MANAGE,
        self::FEEDBACK_NOTES_CREATE,
        self::FEEDBACK_ATTACHMENTS_MANAGE,
        self::FEEDBACK_EXPORT,
        self::FEEDBACK_SUMMARY_VIEW,
    ];

    /** Safe read-only feedback visibility (metadata + summary; never response content). */
    public const FEEDBACK_READ_ONLY = [
        self::FEEDBACK_VIEW,
        self::FEEDBACK_SUMMARY_VIEW,
    ];

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
            ...self::feedbackPermissions(),
            ...self::customerPermissions(),
        ];
    }
}

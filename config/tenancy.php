<?php

declare(strict_types=1);

return [

    /*
    | Public/self-service registration. Disabled by default in Step 6: users arrive only
    | through secure provisioning + invitation (rule 30; ADR 0013). Enabling this requires
    | a Master Source update with onboarding, abuse-prevention, and email controls.
    */
    'registration_enabled' => (bool) env('AUTH_REGISTRATION_ENABLED', false),

    /*
    | Default lifetime (days) of a tenant invitation before it expires.
    */
    'invitation_expiry_days' => (int) env('TENANT_INVITATION_EXPIRY_DAYS', 7),

];

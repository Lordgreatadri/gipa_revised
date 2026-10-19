<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Bootstrap accounts
    |--------------------------------------------------------------------------
    |
    | Credentials live only in the environment. The access seeder creates
    | these accounts when the email and password are both present. Named
    | staff and the sample investor are included only when
    | IOMP_SEED_DEMO_DATA is true. Bulk investor generation is not run.
    |
    */

    'seed_demo_accounts' => (bool) env('IOMP_SEED_DEMO_DATA', false),

    'users' => [
        [
            'name' => env('DEFAULT_SYSTEM_USER_NAME', 'System Administrator'),
            'email' => env('DEFAULT_SYSTEM_USER_EMAIL'),
            'password' => env('DEFAULT_SYSTEM_USER_PASSWORD'),
            'phone' => '+233200000001',
            'role' => 'Super Administrator',
            'user_type' => 'staff',
            'demo' => false,
        ],
        [
            'name' => env('DEMO_SUPER_ADMIN_NAME', 'Super Admin'),
            'email' => env('DEMO_SUPER_ADMIN_EMAIL'),
            'password' => env('DEMO_STAFF_PASSWORD'),
            'phone' => '+233200000002',
            'role' => 'Super Administrator',
            'user_type' => 'staff',
            'demo' => true,
        ],
        [
            'name' => env('DEMO_CONTENT_MANAGER_NAME', 'Content Manager'),
            'email' => env('DEMO_CONTENT_MANAGER_EMAIL'),
            'password' => env('DEMO_STAFF_PASSWORD'),
            'phone' => '+233200000003',
            'role' => 'Content / Data Manager',
            'user_type' => 'staff',
            'demo' => true,
        ],
        [
            'name' => env('DEMO_DISTRICT_OFFICER_NAME', 'District Officer'),
            'email' => env('DEMO_DISTRICT_OFFICER_EMAIL'),
            'password' => env('DEMO_STAFF_PASSWORD'),
            'phone' => '+233200000004',
            'role' => 'District Officer',
            'user_type' => 'staff',
            'demo' => true,
        ],
        [
            'name' => env('DEMO_FIELD_AGENT_NAME', 'Field Agent'),
            'email' => env('DEMO_FIELD_AGENT_EMAIL'),
            'password' => env('DEMO_STAFF_PASSWORD'),
            'phone' => '+233200000005',
            'role' => 'Field Agent',
            'user_type' => 'staff',
            'demo' => true,
        ],
        [
            'name' => env('DEMO_REVIEWER_NAME', 'Reviewer'),
            'email' => env('DEMO_REVIEWER_EMAIL'),
            'password' => env('DEMO_STAFF_PASSWORD'),
            'phone' => '+233200000006',
            'role' => 'Reviewer / Approver',
            'user_type' => 'staff',
            'demo' => true,
        ],
        [
            'name' => env('DEMO_INVESTOR_NAME', 'Investor'),
            'email' => env('DEMO_INVESTOR_EMAIL'),
            'password' => env('DEMO_INVESTOR_PASSWORD'),
            'phone' => '+233200000007',
            'role' => 'Investor',
            'user_type' => 'investor',
            'demo' => true,
        ],
    ],

];

<?php

/*
 * Scorimmo API Configuration
 * https://pro.scorimmo.com/api/doc
 */
return [
    // API
    // https://pro.scorimmo.com/api/doc
    // ----------------------------------------------------------

    // The API endpoint
    'endpoint' => env('SCORIMMO_ENDPOINT', 'https://pro.scorimmo.com/'),

    // The API credentials (used to obtain a JWT token)
    'username' => env('SCORIMMO_USERNAME', ''),
    'password' => env('SCORIMMO_PASSWORD', ''),

    // The API token (if you already have one, bypasses login)
    'api_token' => env('SCORIMMO_API_TOKEN', ''),

    // Webhook API
    // https://pro.scorimmo.com/webhook/doc
    // ----------------------------------------------------------

    /*
     * You can define the job that should be run when a certain webhook hits your application
     * here.
     *
     * Available Scorimmo webhook events:
     * - new_lead
     * - update_lead
     * - new_comment
     * - new_reminder
     * - new_rdv
     * - closure_lead
     *
     * You can use "*" to let a job handle all sent webhook types
     */
    'webhook_jobs' => [
        // 'new_lead' => \App\Jobs\Scorimmo\HandleScorimmoNewLeadJob::class,
        // '*' => \App\Jobs\Scorimmo\HandleAllWebhooks::class
    ],

    /*
     * This model will be used to store all incoming webhooks.
     * It should be or extend `CLDT\Scorimmo\Models\ScorimmoWebhookCall`
     */
    'webhook_model' => \CLDT\Scorimmo\Models\ScorimmoWebhookCall::class,

    // The path where the webhook will be accessible
    'webhook_path' => env('SCORIMMO_WEBHOOK_PATH', '/webhook/scorimmo'),

    // The database table name for storing webhook calls
    'webhook_table_name' => env('SCORIMMO_WEBHOOK_TABLE_NAME', 'scorimmo_webhook_calls'),

    // If you want to verify the webhook signature/token sent by Scorimmo
    'webhook_verify_token' => env('SCORIMMO_WEBHOOK_VERIFY_TOKEN', true),

    // The token/secret to verify the webhook
    'webhook_token' => env('SCORIMMO_WEBHOOK_TOKEN', ''),

    // Prune the webhook calls after X days to keep the database clean
    'webhook_prune_calls_after_days' => env('SCORIMMO_WEBHOOK_PRUNE_CALLS_AFTER_DAYS', 30),
];

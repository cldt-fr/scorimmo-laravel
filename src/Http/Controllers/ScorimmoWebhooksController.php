<?php

namespace CLDT\Scorimmo\Http\Controllers;

use CLDT\Scorimmo\Jobs\ProcessScorimmoWebhookJob;
use CLDT\Scorimmo\ScorimmoWebhookSignatureValidator;
use Illuminate\Http\Request;
use Spatie\WebhookClient\Exceptions\InvalidConfig;
use Spatie\WebhookClient\Exceptions\InvalidWebhookSignature;
use Spatie\WebhookClient\WebhookConfig;
use Spatie\WebhookClient\WebhookProcessor;
use Spatie\WebhookClient\WebhookProfile\ProcessEverythingWebhookProfile;
use Symfony\Component\HttpFoundation\Response;

class ScorimmoWebhooksController
{
    /**
     * @throws InvalidConfig
     */
    public function __invoke(Request $request)
    {
        $webhookConfig = new WebhookConfig([
            'name' => 'Scorimmo',
            'signing_secret' => config('scorimmo.webhook_token'),
            'signature_header_name' => '',
            'signature_validator' => ScorimmoWebhookSignatureValidator::class,
            'webhook_profile' => ProcessEverythingWebhookProfile::class,
            'webhook_model' => config('scorimmo.webhook_model'),
            'process_webhook_job' => ProcessScorimmoWebhookJob::class,
            'store_headers' => [],
        ]);

        try {
            (new WebhookProcessor($request, $webhookConfig))->process();
        } catch (InvalidWebhookSignature $ex) {
            return response()->json(['message' => 'invalid signature'], Response::HTTP_FORBIDDEN);
        }

        return response()->json(['message' => 'ok']);
    }
}

<?php

namespace CLDT\Scorimmo;

use Illuminate\Http\Request;
use Spatie\WebhookClient\SignatureValidator\SignatureValidator;
use Spatie\WebhookClient\WebhookConfig;

class ScorimmoWebhookSignatureValidator implements SignatureValidator
{
    public function isValid(Request $request, WebhookConfig $config): bool
    {
        if (! config('scorimmo.webhook_verify_token')) {
            return true;
        }

        $token = $config->signingSecret;
        $requestToken = $request->header('X-Webhook-Token');

        if (! $requestToken) {
            return false;
        }

        if ($token != $requestToken) {
            return false;
        }

        return true;
    }
}

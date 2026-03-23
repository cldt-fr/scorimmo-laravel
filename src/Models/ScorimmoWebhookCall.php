<?php

namespace CLDT\Scorimmo\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Spatie\WebhookClient\Models\WebhookCall;
use Spatie\WebhookClient\WebhookConfig;

class ScorimmoWebhookCall extends WebhookCall
{
    use MassPrunable;

    public function getTable(): string
    {
        return config('scorimmo.webhook_table_name', parent::getTable());
    }

    public static function storeWebhook(WebhookConfig $config, Request $request): WebhookCall
    {
        $headers = static::headersToStore($config, $request);
        $payload = json_decode($request->getContent(), true) ?? [];

        return static::create([
            'name' => $config->name,
            'url' => $request->fullUrl(),
            'headers' => $headers,
            'payload' => $payload,
            'exception' => null,
        ]);
    }

    public function eventName(): string
    {
        return $this->payload('event') ?? 'unknown';
    }

    public function payload(string $key = null): mixed
    {
        if (! is_null($key)) {
            return Arr::get($this->payload, $key);
        }

        return $this->payload;
    }

    public function prunable(): Builder
    {
        $pruneAfterDays = config('scorimmo.webhook_prune_calls_after_days');

        return static::query()->where('created_at', '<=', now()->subDays($pruneAfterDays));
    }
}

<?php

namespace CLDT\Scorimmo\Jobs;

use function collect;
use function dispatch;
use function event;

use CLDT\Scorimmo\Exceptions\JobClassDoesNotExist;
use CLDT\Scorimmo\Models\ScorimmoWebhookCall;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;
use Spatie\WebhookClient\Models\WebhookCall;

class ProcessScorimmoWebhookJob extends ProcessWebhookJob
{
    public ScorimmoWebhookCall|WebhookCall $webhookCall;

    public function handle()
    {
        event("scorimmo::{$this->webhookCall->eventName()}", $this->webhookCall);

        collect(config('scorimmo.webhook_jobs'))
            ->filter(function (string $jobClassName, $eventActionName) {
                if ($eventActionName === '*') {
                    return true;
                }

                return in_array($eventActionName, [
                    $this->webhookCall->eventName(),
                ]);
            })
            ->each(function (string $jobClassName) {
                if (! class_exists($jobClassName)) {
                    throw JobClassDoesNotExist::make($jobClassName);
                }
            })
            ->each(fn (string $jobClassName) => dispatch(new $jobClassName($this->webhookCall)));
    }
}

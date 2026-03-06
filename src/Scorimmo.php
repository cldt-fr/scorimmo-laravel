<?php

namespace CLDT\Scorimmo;

use CLDT\Scorimmo\Api\Auth;
use CLDT\Scorimmo\Api\Email;
use CLDT\Scorimmo\Api\Lead;
use CLDT\Scorimmo\Api\WebCallback;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Scorimmo
{
    protected PendingRequest $client;

    public function __construct()
    {
        $this->client = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->baseUrl(config('scorimmo.endpoint'));

        $token = config('scorimmo.api_token');

        if ($token) {
            $this->client = $this->client->withToken($token);
        }
    }

    public static function build(): static
    {
        return new static();
    }

    public function withToken(string $token): static
    {
        $this->client = $this->client->withToken($token);

        return $this;
    }

    public function auth(): Auth
    {
        return new Auth($this->client);
    }

    public function lead(): Lead
    {
        return new Lead($this->client);
    }

    public function email(): Email
    {
        return new Email($this->client);
    }

    public function webCallback(): WebCallback
    {
        return new WebCallback($this->client);
    }
}

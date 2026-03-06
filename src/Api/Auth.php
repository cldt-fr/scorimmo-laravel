<?php

namespace CLDT\Scorimmo\Api;

use CLDT\Scorimmo\Helpers\ScorimmoApiResponse;

/**
 * Auth API
 *
 * @see https://pro.scorimmo.com/api/doc
 */
class Auth extends BaseApi
{
    /**
     * Login and get a JWT token
     *
     * @param string|null $username Scorimmo username (defaults to config value)
     * @param string|null $password Scorimmo password (defaults to config value)
     *
     * @return ScorimmoApiResponse
     */
    public function login(?string $username = null, ?string $password = null): ScorimmoApiResponse
    {
        $response = $this->client->post('api/login_check', [
            'username' => $username ?? config('scorimmo.username'),
            'password' => $password ?? config('scorimmo.password'),
        ]);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json());
    }
}

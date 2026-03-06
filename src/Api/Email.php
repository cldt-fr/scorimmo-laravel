<?php

namespace CLDT\Scorimmo\Api;

use CLDT\Scorimmo\Helpers\ScorimmoApiResponse;

/**
 * Email API
 *
 * @see https://pro.scorimmo.com/api/doc
 */
class Email extends BaseApi
{
    /**
     * Send an Email
     *
     * @param array $parameters Email data (to_email, subject, plain, html, title, last_name, first_name, email, phone, external_lead_id, reference)
     *
     * @return ScorimmoApiResponse
     */
    public function send(array $parameters): ScorimmoApiResponse
    {
        $response = $this->client->post('api/email', $parameters);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json());
    }
}

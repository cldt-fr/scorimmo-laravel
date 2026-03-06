<?php

namespace CLDT\Scorimmo\Api;

use CLDT\Scorimmo\Helpers\ScorimmoApiResponse;

/**
 * Web Callback API
 *
 * @see https://pro.scorimmo.com/api/doc
 */
class WebCallback extends BaseApi
{
    /**
     * Launch a callback call
     *
     * @param array $parameters WCB data (key, number_to_call)
     *
     * @return ScorimmoApiResponse
     */
    public function launch(array $parameters): ScorimmoApiResponse
    {
        $response = $this->client->post('api/wcb', $parameters);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json(), 'results');
    }
}

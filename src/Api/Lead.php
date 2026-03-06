<?php

namespace CLDT\Scorimmo\Api;

use CLDT\Scorimmo\Helpers\ScorimmoApiResponse;

/**
 * Lead API
 *
 * @see https://pro.scorimmo.com/api/doc
 */
class Lead extends BaseApi
{
    /**
     * List all Leads
     *
     * @param array $parameters Query parameters (search, order, orderby, limit, page)
     *
     * @return ScorimmoApiResponse
     */
    public function list(array $parameters = []): ScorimmoApiResponse
    {
        $response = $this->client->get('api/leads', $parameters);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json(), 'results');
    }

    /**
     * Get a Lead
     *
     * @param int $id Lead ID
     *
     * @return ScorimmoApiResponse
     */
    public function getOne(int $id): ScorimmoApiResponse
    {
        $response = $this->client->get('api/lead/' . $id);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json());
    }

    /**
     * Create a Lead
     *
     * @param array $parameters Lead data (store_id, properties, customer, interest, origin, seller, comment)
     *
     * @return ScorimmoApiResponse
     */
    public function create(array $parameters): ScorimmoApiResponse
    {
        $response = $this->client->post('api/lead', $parameters);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json());
    }

    /**
     * Update a Lead
     *
     * @param int $id Lead ID
     * @param array $parameters Lead data (customer, seller, comment)
     *
     * @return ScorimmoApiResponse
     */
    public function update(int $id, array $parameters): ScorimmoApiResponse
    {
        $response = $this->client->put('api/lead/' . $id, $parameters);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json());
    }

    /**
     * List Leads by Store
     *
     * @param int $storeId Store ID
     * @param array $parameters Query parameters (search, order, orderby, limit, page)
     *
     * @return ScorimmoApiResponse
     */
    public function listByStore(int $storeId, array $parameters = []): ScorimmoApiResponse
    {
        $response = $this->client->get('api/stores/' . $storeId . '/leads', $parameters);

        return new ScorimmoApiResponse($response->getStatusCode(), $response->json(), 'results');
    }
}

<?php

namespace Redmine\Api;

use Redmine\Client;

/**
 * Abstract class for Api classes
 *
 * @author Thibault Duplessis <thibault.duplessis at gmail dot com>
 * @author Joseph Bielawski <stloyd@gmail.com>
 */
abstract class AbstractApi
{
    /**
     * The client
     *
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritDoc}
     */
    protected function get($path)
    {
        return $this->client->get($path);
    }

    /**
     * {@inheritDoc}
     */
    public function post($path, $data)
    {
        return $this->client->post($path, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function put($path, $data)
    {
        return $this->client->put($path, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function delete($path)
    {
        return $this->client->delete($path);
    }
}

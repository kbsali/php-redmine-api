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

    public function http_build_str($query, $prefix='', $arg_separator='')
    {
        if (!is_array($query)) {
            return null;
        }
        if ('' === $arg_separator) {
            $arg_separator = ini_get('arg_separator.output');
        }
        $args = array();
        foreach ($query as $key => $val) {
            $name = $prefix.$key;
            if (!is_numeric($name)) {
                $args[] = rawurlencode($name).'='.urlencode($val);
            }
        }

        return implode($arg_separator, $args);
    }
}

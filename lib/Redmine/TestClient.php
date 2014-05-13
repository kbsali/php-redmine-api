<?php

namespace Redmine;

class TestClient extends Client
{
    /**
     * @param  string     $path
     * @param  string     $method
     * @param  string     $data
     * @return string
     * @throws \Exception If anything goes wrong on curl request
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        if (in_array($method, array('GET', 'DELETE'))) {
            throw new \Exception('not available');
        }

        return $data;
    }
}

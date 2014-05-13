<?php

namespace Redmine;

class TestUrlClient extends Client
{
    public function get($path)
    {
        return $this->runRequest($path, 'GET');
    }

    /**
     * @param  string     $path
     * @param  string     $method
     * @param  string     $data
     * @return string
     * @throws \Exception If anything goes wrong on curl request
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        return array(
            'path' => $path,
            'method' => $method,
        );
    }
}

<?php

namespace Redmine;

class TestClient extends Client
{
    /**
     * @param  string    $path
     * @throws Exception
     */
    public function get($path)
    {
        throw new \Exception('not available');
    }

    /**
     * returns raw $data
     * @param  string $path
     * @param  string $data
     * @return string $data
     */
    public function post($path, $data)
    {
        return $data;
    }

    /**
     * returns raw $data
     * @param  string $path
     * @param  string $data
     * @return strgin $data
     */
    public function put($path, $data)
    {
        return $data;
    }

    /**
     * @param  string    $path
     * @throws Exception
     */
    public function delete($path)
    {
        throw new \Exception('not available');
    }
}

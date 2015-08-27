<?php

namespace Redmine\Fixtures;

use Redmine\AbstractClient;

/**
 * Mock client class
 * 
 * The runRequest method of this client class just returns the value of
 * the path, method and data or the $runRequestReturnValue value if set.
 */
class MockClient extends AbstractClient
{

    /**
     * Return value the mocked runRequest method should return.
     *
     * @var mixed
     */
    public $runRequestReturnValue = null;

    /**
     * Return value the mocked runRequest method should return.
     *
     * @var mixed
     */
    public $useOriginalGetMethod = false;

    /**
     * Just return the data from runRequest().
     *
     * @param string $path
     * @param bool   $decode
     *
     * @return array
     */
    public function get($path, $decode = true)
    {
        if ($this->useOriginalGetMethod) {
            return parent::get($path, $decode);
        }
        return $this->runRequest($path, 'GET');
    }

    /**
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return string
     *
     * @throws \Exception If anything goes wrong on curl request
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        if (null !== $this->runRequestReturnValue) {
            return $this->runRequestReturnValue;
        }
        return array(
            'path' => $path,
            'method' => $method,
            'data' => $data,
        );
    }
}

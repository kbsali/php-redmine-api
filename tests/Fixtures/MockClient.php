<?php

namespace Redmine\Tests\Fixtures;

use Exception;
use Redmine\Client;

/**
 * Mock client class.
 *
 * The runRequest method of this client class just returns the value of
 * the path, method and data or the $runRequestReturnValue value if set.
 */
class MockClient extends Client
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

    public $responseBodyMock;
    public $responseCodeMock;
    public $responseContentTypeMock;

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
    * Returns status code of the last response.
    *
    * @return int
    */
    public function getLastResponseStatusCode(): int
    {
        return (int) $this->responseCodeMock;
    }

    /**
    * Returns content type of the last response.
    *
    * @return string
    */
    public function getLastResponseContentType(): string
    {
        return (string) $this->responseContentTypeMock;
    }

    /**
     * Returns the body of the last response.
     *
     * @return string
     */
    public function getLastResponseBody(): string
    {
        return (string) $this->responseBodyMock;
    }

    /**
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @throws Exception If anything goes wrong on curl request
     *
     * @return string
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        if (null !== $this->runRequestReturnValue) {
            return $this->runRequestReturnValue;
        }

        $return = [
            'path' => $path,
            'method' => $method,
            'data' => $data,
        ];

        $this->responseBodyMock = json_encode($return);
        $this->responseCodeMock = 200;
        $this->responseContentType = 'application/json';

        return $return;
    }
}

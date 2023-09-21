<?php

namespace Redmine\Tests\Fixtures;

use Exception;
use Redmine\Client\Client;
use Redmine\Client\ClientApiTrait;

/**
 * Mock client class.
 *
 * The runRequest method of this client class just returns the value of
 * the path, method and data or the $runRequestReturnValue value if set.
 */
final class MockClient implements Client
{
    use ClientApiTrait;

    public static function create()
    {
        return new self();
    }

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

    private function __construct() {
    }

    /**
     * Sets to an existing username so api calls can be
     * impersonated to this user.
     */
    public function startImpersonateUser(string $username): void
    {
        throw new Exception('not implemented');
    }

    /**
     * Remove the user impersonate.
     */
    public function stopImpersonateUser(): void
    {
        throw new Exception('not implemented');
    }

    /**
     * Create and send a GET request.
     */
    public function requestGet(string $path): bool
    {
        return $this->runRequest($path, 'GET');
    }

    /**
     * Create and send a POST request.
     */
    public function requestPost(string $path, string $body): bool
    {
        return $this->runRequest($path, 'POST', $body);
    }

    /**
     * Create and send a PUT request.
     */
    public function requestPut(string $path, string $body): bool
    {
        return $this->runRequest($path, 'PUT', $body);
    }

    /**
     * Create and send a DELETE request.
     */
    public function requestDelete(string $path): bool
    {
        return $this->runRequest($path, 'DELETE');
    }

    /**
     * Returns status code of the last response.
     */
    public function getLastResponseStatusCode(): int
    {
        return (int) $this->responseCodeMock;
    }

    /**
     * Returns content type of the last response.
     */
    public function getLastResponseContentType(): string
    {
        return (string) $this->responseContentTypeMock;
    }

    /**
     * Returns the body of the last response.
     */
    public function getLastResponseBody(): string
    {
        return (string) $this->responseBodyMock;
    }

    private function runRequest(string $path, string $method = 'GET', string $data = ''): bool
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
        $this->responseContentTypeMock = 'application/json';

        return true;
    }
}

<?php

namespace Redmine\Client;

use Exception;
use Redmine\Api;

/**
 * Native cURL client
 */
class NativeCurlClient implements Client
{
    use ClientApiTrait;

    private string $url;
    private string $apikeyOrUsername;
    private ?string $password;
    private ?string $impersonateUser = null;
    private int $lastResponseStatusCode = 0;
    private string $lastResponseContentType = '';
    private string $lastResponseBody = '';
    private array $curlOptions = [];

    /**
     * Sets to an existing username so api calls can be
     * impersonated to this user.
     */
    public function startImpersonateUser(string $username): void
    {
        $this->impersonateUser = $username;
    }

    /**
     * Remove the user impersonate.
     */
    public function stopImpersonateUser(): void
    {
        $this->impersonateUser = null;
    }

    /**
     * Create and send a GET request.
     */
    public function requestGet(string $path): bool
    {
        return $this->request('get', $path);
    }

    /**
     * Create and send a POST request.
     */
    public function requestPost(string $path, string $body): bool
    {
        return $this->request('post', $path, $body);
    }

    /**
     * Create and send a PUT request.
     */
    public function requestPut(string $path, string $body): bool
    {
        return $this->request('put', $path, $body);
    }

    /**
     * Create and send a DELETE request.
     */
    public function requestDelete(string $path): bool
    {
        return $this->request('delete', $path);
    }

    /**
    * Returns status code of the last response.
    */
    public function getLastResponseStatusCode(): int
    {
        return $this->lastResponseStatusCode;
    }

    /**
    * Returns content type of the last response.
    */
    public function getLastResponseContentType(): string
    {
        return $this->lastResponseContentType;
    }

    /**
     * Returns the body of the last response.
     */
    public function getLastResponseBody(): string
    {
        return $this->lastResponseBody;
    }

    /**
     * Set a cURL option.
     *
     * @param int   $option The CURLOPT_XXX option to set
     * @param mixed $value  The value to be set on option
     */
    public function setCurlOption(int $option, $value): void
    {
        $this->curlOptions[$option] = $value;
    }

    /**
     * @throws Exception If anything goes wrong on curl request
     */
    private function request(string $method, string $path, string $body = ''): bool
    {
        throw new \Exception(__METHOD__ . ' is not implemented.', 1);

    }
}

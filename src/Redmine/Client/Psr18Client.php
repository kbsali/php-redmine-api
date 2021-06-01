<?php

declare(strict_types=1);

namespace Redmine\Client;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Psr18 client.
 */
final class Psr18Client implements Client
{
    use ClientApiTrait;

    private string $url;
    private string $apikeyOrUsername;
    private ?string $password;
    private ?string $impersonateUser = null;
    private ClientInterface $httpClient;
    private ServerRequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private ?ResponseInterface $lastResponse = null;

    /**
     * $apikeyOrUsername should be your ApiKey, but it could also be your username.
     * $password needs to be set if a username is given (not recommended).
     */
    public function __construct(
        ClientInterface $httpClient,
        ServerRequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $url,
        string $apikeyOrUsername,
        string $password = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->url = $url;
        $this->apikeyOrUsername = $apikeyOrUsername;
        $this->password = $password;
    }

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
        return $this->runRequest('GET', $path);
    }

    /**
     * Create and send a POST request.
     */
    public function requestPost(string $path, string $body): bool
    {
        return $this->runRequest('POST', $path, $body);
    }

    /**
     * Create and send a PUT request.
     */
    public function requestPut(string $path, string $body): bool
    {
        return $this->runRequest('PUT', $path, $body);
    }

    /**
     * Create and send a DELETE request.
     */
    public function requestDelete(string $path): bool
    {
        return $this->runRequest('DELETE', $path);
    }

    /**
     * Returns status code of the last response.
     */
    public function getLastResponseStatusCode(): int
    {
        if (null === $this->lastResponse) {
            return 0;
        }

        return $this->lastResponse->getStatusCode();
    }

    /**
     * Returns content type of the last response.
     */
    public function getLastResponseContentType(): string
    {
        if (null === $this->lastResponse) {
            return '';
        }

        return $this->lastResponse->getHeaderLine('content-type');
    }

    /**
     * Returns the body of the last response.
     */
    public function getLastResponseBody(): string
    {
        if (null === $this->lastResponse) {
            return '';
        }

        return $this->lastResponse->getBody()->__toString();
    }

    /**
     * Create and run a request.
     *
     * @throws Exception If anything goes wrong on the request
     *
     * @return bool true if status code of the response is not 4xx oder 5xx
     */
    private function runRequest(string $method, string $path, string $body = ''): bool
    {
        $this->lastResponse = null;

        $request = $this->createRequest($method, $path, $body);

        try {
            $this->lastResponse = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return $this->lastResponse->getStatusCode() < 400;
    }

    private function createRequest(string $method, string $path, string $body = ''): ServerRequestInterface
    {
        $request = $this->requestFactory->createServerRequest(
            $method,
            $this->url.$path
        );

        // Set Authentication header
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#Authentication
        if (null !== $this->password) {
            $request = $request->withHeader(
                'Authorization',
                'Basic '.base64_encode($this->apikeyOrUsername.':'.$this->password)
            );
        } else {
            $request = $request->withHeader('X-Redmine-API-Key', $this->apikeyOrUsername);
        }

        // Set User Impersonation Header
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#User-Impersonation
        if (null !== $this->impersonateUser) {
            $request = $request->withHeader('X-Redmine-Switch-User', $this->impersonateUser);
        }

        switch ($method) {
            case 'POST':
                if ($this->isUploadCall($path, $body)) {
                    $request = $request->withBody(
                        $this->streamFactory->createStreamFromFile($body)
                    );
                } elseif ('' !== $body) {
                    $request = $request->withBody(
                        $this->streamFactory->createStream($body)
                    );
                }
                break;
            case 'PUT':
                if ('' !== $body) {
                    $request = $request->withBody(
                        $this->streamFactory->createStream($body)
                    );
                }
                break;
        }

        // set Content-Type header
        $tmp = parse_url($this->url.$path);

        if (preg_match('/\/uploads.(json|xml)/i', $path)) {
            $request = $request->withHeader('Content-Type', 'application/octet-stream');
        } elseif ('json' === substr($tmp['path'], -4)) {
            $request = $request->withHeader('Content-Type', 'application/json');
        } elseif ('xml' === substr($tmp['path'], -3)) {
            $request = $request->withHeader('Content-Type', 'text/xml');
        }

        return $request;
    }

    private function isUploadCall(string $path, string $body): bool
    {
        return
            (preg_match('/\/uploads.(json|xml)/i', $path)) &&
            '' !== $body &&
            is_file(strval(str_replace("\0", '', $body)))
        ;
    }
}

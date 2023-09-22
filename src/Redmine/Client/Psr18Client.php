<?php

declare(strict_types=1);

namespace Redmine\Client;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Exception\ClientException;

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
    private RequestFactoryInterface $requestFactory;
    private StreamFactoryInterface $streamFactory;
    private ?ResponseInterface $lastResponse = null;

    /**
     * @param RequestFactoryInterface|ServerRequestFactoryInterface $requestFactory
     * @param string $apikeyOrUsername should be your ApiKey, but it could also be your username.
     * @param ?string $password needs to be set if a username is given (not recommended).
     */
    public function __construct(
        ClientInterface $httpClient,
        $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $url,
        string $apikeyOrUsername,
        string $password = null
    ) {
        if (! $requestFactory instanceof RequestFactoryInterface && $requestFactory instanceof ServerRequestFactoryInterface) {
            @trigger_error(
                sprintf(
                    '%s(): Providing Argument #2 ($requestFactory) as %s is deprecated since v2.3.0, please provide as %s instead.',
                    __METHOD__,
                    ServerRequestFactoryInterface::class,
                    RequestFactoryInterface::class
                ),
                E_USER_DEPRECATED
            );

            $requestFactory = $this->handleServerRequestFactory($requestFactory);
        }

        if (! $requestFactory instanceof RequestFactoryInterface) {
            throw new Exception(sprintf(
                '%s(): Argument #2 ($requestFactory) must be of type %s',
                __METHOD__,
                RequestFactoryInterface::class
            ));
        }

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
     * @throws ClientException If anything goes wrong on the request
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
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }

        return $this->lastResponse->getStatusCode() < 400;
    }

    private function createRequest(string $method, string $path, string $body = ''): RequestInterface
    {
        $request = $this->requestFactory->createRequest(
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
                if ($this->isUploadCall($path) && $this->isValidFilePath($body)) {
                    @trigger_error('Uploading an attachment by filepath is deprecated, use file_get_contents() to upload the file content instead.', E_USER_DEPRECATED);

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

        if ($this->isUploadCall($path)) {
            $request = $request->withHeader('Content-Type', 'application/octet-stream');
        } elseif ('json' === substr($tmp['path'], -4)) {
            $request = $request->withHeader('Content-Type', 'application/json');
        } elseif ('xml' === substr($tmp['path'], -3)) {
            $request = $request->withHeader('Content-Type', 'text/xml');
        }

        return $request;
    }

    /**
     * We accept ServerRequestFactoryInterface for BC
     */
    private function handleServerRequestFactory(ServerRequestFactoryInterface $factory): RequestFactoryInterface
    {
        return new class($factory) implements RequestFactoryInterface
        {
            private ServerRequestFactoryInterface $factory;

            public function __construct(ServerRequestFactoryInterface $factory)
            {
                $this->factory = $factory;
            }

            public function createRequest(string $method, $uri): RequestInterface
            {
                return $this->factory->createServerRequest($method, $uri);
            }
        };
    }
}

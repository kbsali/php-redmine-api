<?php

declare(strict_types=1);

namespace Redmine\Client;

use Exception;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Exception\ClientException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Http\Request;
use Redmine\Http\Response;

/**
 * Psr18 client.
 */
final class Psr18Client implements Client, HttpClient
{
    use ClientApiTrait;
    private ?string $impersonateUser = null;
    private RequestFactoryInterface $requestFactory;

    /**
     * @param RequestFactoryInterface $requestFactory
     * @param string $apikeyOrUsername should be your ApiKey, but it could also be your username.
     * @param ?string $password needs to be set if a username is given (not recommended).
     */
    public function __construct(
        private ClientInterface $httpClient,
        $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $url,
        private string $apikeyOrUsername,
        private ?string $password = null,
    ) {
        if (! $requestFactory instanceof RequestFactoryInterface) {
            throw new Exception(sprintf(
                '%s(): Argument #2 ($requestFactory) must be of type %s',
                __METHOD__,
                RequestFactoryInterface::class,
            ));
        }

        $this->requestFactory = $requestFactory;
    }

    /**
     * Create and send a HTTP request and return the response
     *
     * @throws ClientException If anything goes wrong on creating or sending the request
     */
    public function request(Request $request): Response
    {
        $response = $this->runRequest(
            $request->getMethod(),
            $request->getPath(),
            $request->getContent(),
            $request->getContentType(),
        );

        return HttpFactory::makeResponse(
            $response->getStatusCode(),
            $response->getHeaderLine('Content-Type'),
            strval($response->getBody()),
        );
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
     * Create and run a request.
     *
     * @throws ClientException If anything goes wrong on the request
     */
    private function runRequest(string $method, string $path, string $body = '', string $contentType = ''): ResponseInterface
    {
        $request = $this->createRequest($method, $path, $body, $contentType);

        try {
            return $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function createRequest(string $method, string $path, string $body = '', string $contentType = ''): RequestInterface
    {
        $request = $this->requestFactory->createRequest(
            $method,
            $this->url . $path,
        );

        // Set Authentication header
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#Authentication
        if (null !== $this->password) {
            $request = $request->withHeader(
                'Authorization',
                'Basic ' . base64_encode($this->apikeyOrUsername . ':' . $this->password),
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
            case 'PUT':
                if ('' !== $body) {
                    $request = $request->withBody(
                        $this->streamFactory->createStream($body),
                    );
                }
                break;
        }

        // set Content-Type header
        if ($contentType !== '') {
            $request = $request->withHeader('Content-Type', $contentType);
        } else {
            // guess Content-Type from path
            $tmp = parse_url($this->url . $path);

            if ($this->isUploadCall($path)) {
                $request = $request->withHeader('Content-Type', 'application/octet-stream');
            } elseif (str_ends_with($tmp['path'], 'json')) {
                $request = $request->withHeader('Content-Type', 'application/json');
            } elseif (str_ends_with($tmp['path'], 'xml')) {
                $request = $request->withHeader('Content-Type', 'text/xml');
            }
        }

        return $request;
    }
}

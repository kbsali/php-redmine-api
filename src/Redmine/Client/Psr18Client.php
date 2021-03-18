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

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $apikeyOrUsername;

    /**
     * @var string|null
     */
    private $pass;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var ServerRequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @var ResponseInterface|null
     */
    private $lastResponse;

    /**
     * Usage: apikeyOrUsername can be auth key or username.
     * Password needs to be set if username is given.
     *
     * @param string      $url
     * @param string      $apikeyOrUsername
     * @param string|null $pass
     */
    public function __construct(
        ClientInterface $httpClient,
        ServerRequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $url,
        string $apikeyOrUsername,
        string $pass = null
    ) {
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->streamFactory = $streamFactory;
        $this->url = $url;
        $this->apikeyOrUsername = $apikeyOrUsername;
        $this->pass = $pass;
    }

    /**
     * HTTP GETs a json $path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function requestGet(string $path): bool
    {
        return $this->runRequest('get', $path);
    }

    /**
     * HTTP POSTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function requestPost(string $path, string $data): bool
    {
        return $this->runRequest('post', $path, $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function requestPut(string $path, string $data): bool
    {
        return $this->runRequest('put', $path, $data);
    }

    /**
     * HTTP PUTs $params to $path.
     *
     * @param string $path
     *
     * @return bool
     */
    public function requestDelete(string $path): bool
    {
        return $this->runRequest('delete', $path);
    }

    /**
    * Returns status code of the last response.
    *
    * @return int
    */
    public function getLastResponseStatusCode(): int
    {
        if ($this->lastResponse === null) {
            return 0;
        }

        return $this->lastResponse->getStatusCode();
    }

    /**
    * Returns content type of the last response.
    *
    * @return string
    */
    public function getLastResponseContentType(): string
    {
        if ($this->lastResponse === null) {
            return '';
        }

        return $this->lastResponse->getHeaderLine('content-type');
    }

    /**
     * Returns the body of the last response.
     *
     * @return string
     */
    public function getLastResponseBody(): string
    {
        if ($this->lastResponse === null) {
            return '';
        }

        return $this->lastResponse->getBody()->__toString();
    }

    /**
     * Create an run a request
     *
     * @param string $method
     * @param string $path
     * @param string $data
     *
     * @throws Exception If anything goes wrong on the request
     *
     * @return bool true if status code of the response is not 4xx oder 5xx
     */
    private function runRequest(string $method, string $path, string $data = ''): bool
    {
        $this->lastResponse = null;

        $request = $this->createRequest($method, $path, $data);

        try {
            $this->lastResponse = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }

        return ($this->lastResponse->getStatusCode() < 400);
    }

    private function createRequest(string $method, string $path, string $data = ''): ServerRequestInterface
    {
        $request = $this->requestFactory->createServerRequest(
            $method,
            $this->url . $path
        );

        if ($this->pass !== null) {
            $request = $request->withHeader(
                'Authorization',
                'Basic ' . base64_encode($this->apikeyOrUsername . ':' . $this->pass)
            );
        } else {
            $request = $request->withHeader('X-Redmine-API-Key', $this->apikeyOrUsername);
        }

        switch ($method) {
            case 'post':
                if ($this->isUploadCall($path, $data)) {
                    $request = $request->withBody(
                        $this->streamFactory->createStreamFromFile($data)
                    );
                } elseif ($data !== '') {
                    $request = $request->withBody(
                        $this->streamFactory->createStream($data)
                    );
                }
                break;
            case 'put':
                if ($data !== '') {
                    $request = $request->withBody(
                        $this->streamFactory->createStream($data)
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

    private function isUploadCall(string $path, string $data): bool
    {
        return
            (preg_match('/\/uploads.(json|xml)/i', $path)) &&
            $data !== '' &&
            is_file(strval(str_replace("\0", '', $data)))
        ;
    }
}

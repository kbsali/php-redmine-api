<?php

namespace Redmine;

use Psr\Http\Client\ClientInterface as PsrClient;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Psr18 client.
 */
final class Psr18Client implements ClientInterface
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
     * @var PsrClient
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
     * Usage: apikeyOrUsername can be auth key or username.
     * Password needs to be set if username is given.
     *
     * @param string      $url
     * @param string      $apikeyOrUsername
     * @param string|null $pass
     */
    public function __construct(
        PsrClient $httpClient,
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
}

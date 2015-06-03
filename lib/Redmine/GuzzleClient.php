<?php

namespace Redmine;

use GuzzleHttp\Client as GuzzleHttpClient;
use Redmine\Api\SimpleXMLElement;

/**
 * Simple PHP Redmine client.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 * Website: http://github.com/kbsali/php-redmine-api
 */
class GuzzleClient extends AbstractClient
{
    /** @var \GuzzleHttp\Client $client */
    protected $client;

    public function __construct($url, $apikeyOrUsername, $pass = null)
    {
        parent::__construct($url, $apikeyOrUsername, $pass);
        $this->client = new GuzzleHttpClient();
    }

    /**
     * @codeCoverageIgnore Ignore due to untestable guzzle function calls.
     *
     * @param string $path
     * @param string $method
     * @param string $data
     *
     * @return bool|SimpleXMLElement|string
     *
     * @throws \Exception If anything goes wrong on request
     */
    protected function runRequest($path, $method = 'GET', $data = '')
    {
        $this->responseCode = null;
        $options = array();

        // HTTP Basic Authentication
        if ($this->apikeyOrUsername && $this->useHttpAuth) {
            if (null === $this->pass) {
                $options['auth'] = [$this->apikeyOrUsername, rand(100000, 199999)];
            } else {
                $options['auth'] = [$this->apikeyOrUsername, $this->pass];
            }
        }

        // Host and request options
        // todo: set port and verify host?!
        $options['verify'] = $this->checkSslCertificate;

        // Set the HTTP request headers
        $options['headers'] = $this->generateHttpHeader($path);

        switch ($method) {
            case 'POST':
                if (isset($data)) {
                    $options['body'] = $data;
                }
                break;
            case 'PUT':
                if (isset($data)) {
                    $options['body'] = $data;
                }
                break;
            case 'DELETE':
                break;
            default: // GET
                break;
        }

        return $this->getClient()->request(
            $method,
            $this->url.$path,
            $options
        );
    }

    /**
     * Decodes json response.
     *
     * Since this is automatically done by Guzzle, do nothing.
     *
     * @param string $json
     *
     * @return string
     */
    public function decode($json)
    {
        return $json;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param \GuzzleHttp\Client $client
     *
     * @return $this
     */
    public function setClient($client)
    {
        $this->client = $client;

        return $this;
    }
}
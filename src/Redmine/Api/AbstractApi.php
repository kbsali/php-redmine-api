<?php

declare(strict_types=1);

namespace Redmine\Api;

use InvalidArgumentException;
use Redmine\Api;
use Redmine\Client\Client;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Http\Request;
use Redmine\Http\Response;
use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;
use SimpleXMLElement;

/**
 * Abstract class for Api classes.
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
abstract class AbstractApi implements Api
{
    use ResponseValidationTrait;

    /**
     * @var Client
     */
    protected $client;

    private \Redmine\Http\HttpClient $httpClient;

    /**
     * Enabled by the client via `enableFutureMode()`.
     */
    private bool $futureMode = false;

    /**
     * @var Response
     */
    protected $lastResponse;

    /**
     * @param HttpClient $client
     */
    public function __construct($client)
    {
        if (! $client instanceof Client && ! $client instanceof HttpClient) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($client) must be of type %s or %s, `%s` given',
                __METHOD__,
                Client::class,
                HttpClient::class,
                get_debug_type($client),
            ));
        }

        if ($client instanceof Client) {
            $this->client = $client;
        }

        if (! $client instanceof HttpClient) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($client) of type %s must also implement %s, `%s` given',
                __METHOD__,
                Client::class,
                HttpClient::class,
                get_debug_type($client),
            ));
        }

        $this->httpClient = $client;
    }

    final protected function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    final protected function isFutureModeEnabled(): bool
    {
        return $this->futureMode;
    }

    final public function getLastResponse(): Response
    {
        return ($this->lastResponse instanceof Response) ? $this->lastResponse : HttpFactory::makeResponse(0, '', '');
    }

    /**
     * Perform the client get() method.
     *
     * @deprecated v2.6.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     *
     * @param string $path
     * @param bool   $decodeIfJson
     *
     * @return string|array<mixed>|SimpleXMLElement|false
     */
    protected function get($path, $decodeIfJson = true)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.', E_USER_DEPRECATED);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'GET',
            strval($path),
            $this->getContentTypeFromPath(strval($path)),
        ));

        $body = $this->lastResponse->getContent();
        $contentType = $this->lastResponse->getContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && str_starts_with($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        if (true === $decodeIfJson && '' !== $body && str_starts_with($contentType, 'application/json')) {
            try {
                return JsonSerializer::createFromString($body)->getNormalized();
            } catch (SerializerException $e) {
                return 'Error decoding body as JSON: ' . $e->getPrevious()->getMessage();
            }
        }

        return ('' === $body) ? false : $body;
    }

    /**
     * Perform the client post() method.
     *
     * @deprecated v2.6.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     *
     * @param string $path
     * @param string $data
     *
     * @return string|SimpleXMLElement|false
     */
    protected function post($path, $data)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.', E_USER_DEPRECATED);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'POST',
            strval($path),
            $this->getContentTypeFromPath(strval($path)),
            $data,
        ));

        $body = $this->lastResponse->getContent();
        $contentType = $this->lastResponse->getContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && str_starts_with($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Perform the client put() method.
     *
     * @deprecated v2.6.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     *
     * @param string $path
     * @param string $data
     *
     * @return string|SimpleXMLElement
     */
    protected function put($path, $data)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.', E_USER_DEPRECATED);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'PUT',
            strval($path),
            $this->getContentTypeFromPath(strval($path)),
            $data,
        ));

        $body = $this->lastResponse->getContent();
        $contentType = $this->lastResponse->getContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && str_starts_with($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Perform the client delete() method.
     *
     * @deprecated v2.6.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     *
     * @param string $path
     *
     * @return string
     */
    protected function delete($path)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.', E_USER_DEPRECATED);

        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'DELETE',
            strval($path),
            $this->getContentTypeFromPath(strval($path)),
        ));

        return $this->lastResponse->getContent();
    }

    /**
     * Retrieves as many elements as you want of a given endpoint (even if the
     * total number of elements is greater than 100).
     *
     * @param string       $endpoint API end point
     * @param array<mixed> $params   optional query parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if the status code is not 200 (only if future mode is enabled)
     * @throws SerializerException if response body could not be converted into array
     *
     * @return array<mixed> elements found
     */
    protected function retrieveData(string $endpoint, array $params = []): array
    {
        if ($params === []) {
            return $this->requestDataAsArray($endpoint);
        }

        $params = array_filter(
            array_merge(
                [
                    'limit' => 25,
                    'offset' => 0,
                ],
                $params,
            ),
            static fn($value): bool => false !== $value
                && null !== $value
                && '' !== $value
                && (!is_array($value) && !is_object($value) || !empty($value)),
        );

        $returnData = [];

        // Redmine max limit is 100,
        // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#Collection-resources-and-pagination
        $redmineLimit = 100;
        $requestedLimit = $remaininglimit = $params['limit'];
        $offset = $params['offset'];

        while ($remaininglimit > 0) {
            if ($remaininglimit > $redmineLimit) {
                $realLimit = $redmineLimit;
                $remaininglimit -= $redmineLimit;
            } else {
                $realLimit = $remaininglimit;
                $remaininglimit = 0;
            }
            $params['limit'] = $realLimit;
            $params['offset'] = $offset;

            $newDataSet = $this->requestDataAsArray(PathSerializer::create($endpoint, $params)->getPath());

            $returnData = array_merge_recursive($returnData, $newDataSet);

            // After the first request we know the total_count for this endpoint
            // so lets use the total_count to correct $requestedLimit to save us
            // from making unnecessary requests
            // e.g. total_count = 5 and $requestedLimit = 500 will make only 1 request instead of 2
            if (isset($newDataSet['total_count']) && $newDataSet['total_count'] < $requestedLimit) {
                $requestedLimit = $remaininglimit = (int) $newDataSet['total_count'];

                if ($remaininglimit > $redmineLimit) {
                    $realLimit = $redmineLimit;
                    $remaininglimit -= $redmineLimit;
                } else {
                    $realLimit = $remaininglimit;
                    $remaininglimit = 0;
                }
            }

            $offset += $realLimit;

            if (
                $newDataSet === []
                || !isset($newDataSet['limit'])
                || (
                    isset($newDataSet['offset'])
                    && isset($newDataSet['total_count'])
                    && $newDataSet['offset'] >= $newDataSet['total_count']
                )
            ) {
                $remaininglimit = 0;
            }
        }

        return $returnData;
    }

    /**
     * Requests the path and returns the response body as array.
     *
     * @throws UnexpectedResponseException if the status code is not 200 (only if future mode is enabled)
     * @throws SerializerException if response body could not be converted into array
     *
     * @return array<mixed>
     */
    private function requestDataAsArray(string $path): array
    {
        $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
            'GET',
            $path,
            $this->getContentTypeFromPath($path),
        ));

        if ($this->isFutureModeEnabled()) {
            $this->throwOnUnexpectedStatusCode(200);
        }

        return $this->getResponseAsArray($this->lastResponse);
    }

    /**
     * returns the last response body as array.
     *
     * @throws SerializerException if response body could not be converted into array
     *
     * @return array<mixed>
     */
    private function getResponseAsArray(Response $response): array
    {
        $body = $response->getContent();
        $contentType = $response->getContentType();
        $returnData = null;

        // parse XML
        if (str_starts_with($contentType, 'application/xml')) {
            $returnData = XmlSerializer::createFromString($body)->getNormalized();
        } elseif (str_starts_with($contentType, 'application/json')) {
            $returnData = JsonSerializer::createFromString($body)->getNormalized();
        }

        if (!is_array($returnData)) {
            throw new SerializerException('Could not convert response body into array: ' . $body);
        }

        return $returnData;
    }

    private function getContentTypeFromPath(string $path): string
    {
        $tmp = parse_url($path);

        $path = strtolower($path);

        if (str_contains($path, '/uploads.json') || str_contains($path, '/uploads.xml')) {
            return 'application/octet-stream';
        }

        if (str_ends_with($tmp['path'], 'json')) {
            return 'application/json';
        }

        if (str_ends_with($tmp['path'], 'xml')) {
            return 'application/xml';
        }

        return '';
    }
}

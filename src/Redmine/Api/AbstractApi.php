<?php

namespace Redmine\Api;

use Closure;
use InvalidArgumentException;
use Redmine\Api;
use Redmine\Client\Client;
use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Http\HttpClient;
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
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var Response
     */
    private $lastResponse;

    /**
     * @param Client|HttpClient $client
     */
    public function __construct($client)
    {
        if (! is_object($client) || (! $client instanceof Client && ! $client instanceof HttpClient)) {
            throw new InvalidArgumentException(sprintf(
                '%s(): Argument #1 ($client) must be of type %s or %s, `%s` given',
                __METHOD__,
                Client::class,
                HttpClient::class,
                (is_object($client)) ? get_class($client) : gettype($client)
            ));
        }

        if ($client instanceof Client) {
            $this->client = $client;
        }

        $httpClient = $client;

        if (! $httpClient instanceof HttpClient) {
            $httpClient = $this->handleClient($client);
        }

        $this->httpClient = $httpClient;
    }

    final protected function getHttpClient(): HttpClient
    {
        return $this->httpClient;
    }

    final protected function getLastResponse(): Response
    {
        return $this->lastResponse !== null ? $this->lastResponse : $this->createResponse(0, '', '');
    }

    /**
     * Returns whether or not the last api call failed.
     *
     * @return bool
     *
     * @deprecated since v2.1.0, because it does not correctly handle 2xx codes that are not 200 or 201, use \Redmine\Client\Client::getLastResponseStatusCode() instead
     * @see Client::getLastResponseStatusCode() for checking the status code directly
     */
    public function lastCallFailed()
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.1.0, use \Redmine\Client\Client::getLastResponseStatusCode() instead.', E_USER_DEPRECATED);

        if ($this->lastResponse !== null) {
            $code = $this->lastResponse->getStatusCode();
        } elseif ($this->client !== null) {
            $code = $this->client->getLastResponseStatusCode();
        } else {
            $code = 0;
        }

        return 200 !== $code && 201 !== $code;
    }

    /**
     * Perform the client get() method.
     *
     * @param string $path
     * @param bool   $decodeIfJson
     *
     * @return string|array|SimpleXMLElement|false
     */
    protected function get($path, $decodeIfJson = true)
    {
        $this->lastResponse = $this->getHttpClient()->request('GET', strval($path));

        $body = $this->lastResponse->getBody();
        $contentType = $this->lastResponse->getContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && 0 === strpos($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        if (true === $decodeIfJson && '' !== $body && 0 === strpos($contentType, 'application/json')) {
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
     * @param string $path
     * @param string $data
     *
     * @return string|SimpleXMLElement|false
     */
    protected function post($path, $data)
    {
        $this->lastResponse = $this->getHttpClient()->request('POST', strval($path), $data);

        $body = $this->lastResponse->getBody();
        $contentType = $this->lastResponse->getContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && 0 === strpos($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Perform the client put() method.
     *
     * @param string $path
     * @param string $data
     *
     * @return string|SimpleXMLElement
     */
    protected function put($path, $data)
    {
        $this->lastResponse = $this->getHttpClient()->request('PUT', strval($path), $data);

        $body = $this->lastResponse->getBody();
        $contentType = $this->lastResponse->getContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && 0 === strpos($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Perform the client delete() method.
     *
     * @param string $path
     *
     * @return string
     */
    protected function delete($path)
    {
        $this->lastResponse = $this->getHttpClient()->request('DELETE', strval($path));

        return $this->lastResponse->getBody();
    }

    /**
     * Checks if the variable passed is not null.
     *
     * @param mixed $var Variable to be checked
     *
     * @return bool
     */
    protected function isNotNull($var)
    {
        return
            false !== $var &&
            null !== $var &&
            '' !== $var &&
            !((is_array($var) || is_object($var)) && empty($var));
    }

    /**
     * @return array
     */
    protected function sanitizeParams(array $defaults, array $params)
    {
        return array_filter(
            array_merge($defaults, $params),
            [$this, 'isNotNull']
        );
    }

    /**
     * Retrieves all the elements of a given endpoint (even if the
     * total number of elements is greater than 100).
     *
     * @deprecated since v2.2.0, use `retrieveData()` instead
     *
     * @param string $endpoint API end point
     * @param array  $params   optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false elements found or error message or false
     */
    protected function retrieveAll($endpoint, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.2.0, use `retrieveData()` instead.', E_USER_DEPRECATED);

        try {
            $data = $this->retrieveData(strval($endpoint), $params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getBody() === '') {
                return false;
            }

            return $e->getMessage();
        }

        return $data;
    }

    /**
     * Retrieves as many elements as you want of a given endpoint (even if the
     * total number of elements is greater than 100).
     *
     * @param string $endpoint API end point
     * @param array  $params   optional query parameters to be passed to the api (offset, limit, ...)
     *
     * @throws SerializerException if response body could not be converted into array
     *
     * @return array elements found
     */
    protected function retrieveData(string $endpoint, array $params = []): array
    {
        if (empty($params)) {
            $this->lastResponse = $this->getHttpClient()->request('GET', strval($endpoint));

            return $this->getResponseAsArray($this->lastResponse);
        }

        $params = $this->sanitizeParams(
            [
                'limit' => 25,
                'offset' => 0,
            ],
            $params
        );

        $returnData = [];

        $limit = $params['limit'];
        $offset = $params['offset'];

        while ($limit > 0) {
            if ($limit > 100) {
                $_limit = 100;
                $limit -= 100;
            } else {
                $_limit = $limit;
                $limit = 0;
            }
            $params['limit'] = $_limit;
            $params['offset'] = $offset;

            $this->lastResponse = $this->getHttpClient()->request(
                'GET',
                PathSerializer::create($endpoint, $params)->getPath()
            );

            $newDataSet = $this->getResponseAsArray($this->lastResponse);

            $returnData = array_merge_recursive($returnData, $newDataSet);

            $offset += $_limit;

            if (
                empty($newDataSet) || !isset($newDataSet['limit']) || (
                    isset($newDataSet['offset']) &&
                    isset($newDataSet['total_count']) &&
                    $newDataSet['offset'] >= $newDataSet['total_count']
                )
            ) {
                $limit = 0;
            }
        }

        return $returnData;
    }

    /**
     * Attaches Custom Fields to a create/update query.
     *
     * @deprecated since v2.3.0, use `\Redmine\Serializer\XmlSerializer::createFromArray()` instead
     *
     * @param SimpleXMLElement $xml    XML Element the custom fields are attached to
     * @param array            $fields array of fields to attach, each field needs name, id and value set
     *
     * @return SimpleXMLElement $xml
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_api#Working-with-custom-fields
     */
    protected function attachCustomFieldXML(SimpleXMLElement $xml, array $fields)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.3.0, use `\Redmine\Serializer\XmlSerializer::createFromArray()` instead.', E_USER_DEPRECATED);

        $_fields = $xml->addChild('custom_fields');
        $_fields->addAttribute('type', 'array');
        foreach ($fields as $field) {
            $_field = $_fields->addChild('custom_field');

            if (isset($field['name'])) {
                $_field->addAttribute('name', $field['name']);
            }
            if (isset($field['field_format'])) {
                $_field->addAttribute('field_format', $field['field_format']);
            }
            $_field->addAttribute('id', $field['id']);
            if (array_key_exists('value', $field) && is_array($field['value'])) {
                $_field->addAttribute('multiple', 'true');
                $_values = $_field->addChild('value');
                if (array_key_exists('token', $field['value'])) {
                    foreach ($field['value'] as $key => $val) {
                        $_values->addChild($key, $val);
                    }
                } else {
                    $_values->addAttribute('type', 'array');
                    foreach ($field['value'] as $val) {
                        $_values->addChild('value', $val);
                    }
                }
            } else {
                $_field->value = $field['value'];
            }
        }

        return $xml;
    }

    /**
     * returns the last response body as array.
     *
     * @throws SerializerException if response body could not be converted into array
     */
    private function getResponseAsArray(Response $response): array
    {
        $body = $response->getBody();
        $contentType = $response->getContentType();
        $returnData = null;

        // parse XML
        if (0 === strpos($contentType, 'application/xml')) {
            $returnData = XmlSerializer::createFromString($body)->getNormalized();
        } elseif (0 === strpos($contentType, 'application/json')) {
            $returnData = JsonSerializer::createFromString($body)->getNormalized();
        }

        if (!is_array($returnData)) {
            throw new SerializerException('Could not convert response body into array: ' . $body);
        }

        return $returnData;
    }

    private function handleClient(Client $client): HttpClient
    {
        $responseFactory = Closure::fromCallable([$this, 'createResponse']);

        return new class ($client, $responseFactory) implements HttpClient {
            private $client;
            private $responseFactory;

            public function __construct(Client $client, Closure $responseFactory)
            {
                $this->client = $client;
                $this->responseFactory = $responseFactory;
            }

            public function request(string $method, string $path, string $body = ''): Response
            {
                if ($method === 'POST') {
                    $this->client->requestPost($path, $body);
                } elseif ($method === 'PUT') {
                    $this->client->requestPut($path, $body);
                } elseif ($method === 'DELETE') {
                    $this->client->requestDelete($path);
                } else {
                    $this->client->requestGet($path);
                }

                return ($this->responseFactory)(
                    $this->client->getLastResponseStatusCode(),
                    $this->client->getLastResponseContentType(),
                    $this->client->getLastResponseBody()
                );
            }
        };
    }

    private function createResponse(int $statusCode, string $contentType, string $body): Response
    {
        return new class ($statusCode, $contentType, $body) implements Response {
            private $statusCode;
            private $contentType;
            private $body;

            public function __construct(int $statusCode, string $contentType, string $body)
            {
                $this->statusCode = $statusCode;
                $this->contentType = $contentType;
                $this->body = $body;
            }

            public function getStatusCode(): int
            {
                return $this->statusCode;
            }

            public function getContentType(): string
            {
                return $this->contentType;
            }

            public function getBody(): string
            {
                return $this->body;
            }
        };
    }
}

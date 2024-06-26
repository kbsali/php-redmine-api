<?php

namespace Redmine\Api;

use InvalidArgumentException;
use Redmine\Api;
use Redmine\Client\Client;
use Redmine\Exception;
use Redmine\Exception\SerializerException;
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
    protected $lastResponse;

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
                (is_object($client)) ? get_class($client) : gettype($client),
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

    final public function getLastResponse(): Response
    {
        return $this->lastResponse !== null ? $this->lastResponse : HttpFactory::makeResponse(0, '', '');
    }

    /**
     * Returns whether or not the last api call failed.
     *
     * @return bool
     *
     * @deprecated v2.1.0 It does not correctly handle 2xx codes that are not 200 or 201, use `Redmine\Api\AbstractApi::getLastResponse()->getStatusCode()` instead
     * @see AbstractApi::getLastResponse()->getStatusCode() for checking the status code directly
     */
    public function lastCallFailed()
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.1.0, use \Redmine\Api\AbstractApi::getLastResponse()->getStatusCode() instead.', E_USER_DEPRECATED);

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
     * @deprecated v2.6.0 Use `\Redmine\Http\HttpClient::request()` instead
     * @see \Redmine\Http\HttpClient::request()
     *
     * @param string $path
     * @param bool   $decodeIfJson
     *
     * @return string|array|SimpleXMLElement|false
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
        if ('' !== $body && 0 === strpos($contentType, 'application/xml')) {
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
        if ('' !== $body && 0 === strpos($contentType, 'application/xml')) {
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
            [$this, 'isNotNull'],
        );
    }

    /**
     * Retrieves all the elements of a given endpoint (even if the
     * total number of elements is greater than 100).
     *
     * @deprecated v2.2.0 Use `retrieveData()` instead
     * @see AbstractApi::retrieveData()
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
            if ($this->getLastResponse()->getContent() === '') {
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
            $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
                'GET',
                strval($endpoint),
                $this->getContentTypeFromPath(strval($endpoint)),
            ));

            return $this->getResponseAsArray($this->lastResponse);
        }

        $params = $this->sanitizeParams(
            [
                'limit' => 25,
                'offset' => 0,
            ],
            $params,
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

            $this->lastResponse = $this->getHttpClient()->request(HttpFactory::makeRequest(
                'GET',
                PathSerializer::create($endpoint, $params)->getPath(),
                $this->getContentTypeFromPath($endpoint),
            ));

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
     * @deprecated v2.3.0 Use `\Redmine\Serializer\XmlSerializer::createFromArray()` instead
     * @see \Redmine\Serializer\XmlSerializer::createFromArray()
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
        $body = $response->getContent();
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
        return new class ($client) implements HttpClient {
            private $client;

            public function __construct(Client $client)
            {
                $this->client = $client;
            }

            public function request(Request $request): Response
            {
                if ($request->getMethod() === 'POST') {
                    $this->client->requestPost($request->getPath(), $request->getContent());
                } elseif ($request->getMethod() === 'PUT') {
                    $this->client->requestPut($request->getPath(), $request->getContent());
                } elseif ($request->getMethod() === 'DELETE') {
                    $this->client->requestDelete($request->getPath());
                } else {
                    $this->client->requestGet($request->getPath());
                }

                return HttpFactory::makeResponse(
                    $this->client->getLastResponseStatusCode(),
                    $this->client->getLastResponseContentType(),
                    $this->client->getLastResponseBody(),
                );
            }
        };
    }

    private function getContentTypeFromPath(string $path): string
    {
        $tmp = parse_url($path);

        $path = strtolower($path);

        if (false !== strpos($path, '/uploads.json') || false !== strpos($path, '/uploads.xml')) {
            return 'application/octet-stream';
        } elseif ('json' === substr($tmp['path'], -4)) {
            return 'application/json';
        } elseif ('xml' === substr($tmp['path'], -3)) {
            return 'application/xml';
        } else {
            return '';
        }
    }
}

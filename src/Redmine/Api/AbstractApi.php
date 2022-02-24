<?php

namespace Redmine\Api;

use JsonException;
use Redmine\Api;
use Redmine\Client\Client;
use Redmine\Exception\ClientException;
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

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Returns whether or not the last api call failed.
     *
     * @return bool
     *
     * @deprecated This method does not correctly handle 2xx codes that are not 200 or 201, use \Redmine\Client\Client::getLastResponseStatusCode() instead
     * @see Client::getLastResponseStatusCode() for checking the status code directly
     */
    public function lastCallFailed()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use \Redmine\Client\Client::getLastResponseStatusCode() instead.', E_USER_DEPRECATED);

        $code = $this->client->getLastResponseStatusCode();

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
        $this->client->requestGet(strval($path));

        $body = $this->client->getLastResponseBody();
        $contentType = $this->client->getLastResponseContentType();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && 0 === strpos($contentType, 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        if (true === $decodeIfJson && '' !== $body && 0 === strpos($contentType, 'application/json')) {
            try {
                return json_decode($body, true, 512, \JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                // TODO: Throw Exception instead of returning string
                return 'Error decoding body as JSON: '.$e->getMessage();
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
     * @return string|false
     */
    protected function post($path, $data)
    {
        $this->client->requestPost($path, $data);

        $body = $this->client->getLastResponseBody();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && 0 === strpos($this->client->getLastResponseContentType(), 'application/xml')) {
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
     * @return string|false
     */
    protected function put($path, $data)
    {
        $this->client->requestPut($path, $data);

        $body = $this->client->getLastResponseBody();

        // if response is XML, return a SimpleXMLElement object
        if ('' !== $body && 0 === strpos($this->client->getLastResponseContentType(), 'application/xml')) {
            return new SimpleXMLElement($body);
        }

        return $body;
    }

    /**
     * Perform the client delete() method.
     *
     * @param string $path
     *
     * @return false|SimpleXMLElement|string
     */
    protected function delete($path)
    {
        $this->client->requestDelete($path);

        return $this->client->getLastResponseBody();
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
     * @deprecated the `retrieveAll()` method is deprecated, use `retrieveData()` instead.
     *
     * @param string $endpoint API end point
     * @param array  $params   optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array elements found
     */
    protected function retrieveAll($endpoint, array $params = [])
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use `retrieveData()` instead.', E_USER_DEPRECATED);

        $data = $this->retrieveData(strval($endpoint), $params);

        if (! array_key_exists('response', $data)) {
            return $data;
        }

        return ('' === $data['response']) ? false : $data['response'];
    }

    /**
     * Retrieves all the elements of a given endpoint (even if the
     * total number of elements is greater than 100).
     *
     * @param string $endpoint API end point
     * @param array  $params   optional query parameters to be passed to the api (offset, limit, ...)
     *
     * @return array elements found
     */
    protected function retrieveData(string $endpoint, array $params = []): array
    {
        if (empty($params)) {
            $this->client->requestGet($endpoint);

            return $this->getLastResponseAsArray();
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

            $queryString = http_build_query($params);
            // replace every encoded array (`foo[0]=`, `foo[1]=`, `foo[2]=`, etc => `foo[]=`)
            $queryString = preg_replace('/%5B[0-9]+%5D/simU', '%5B%5D', $queryString);

            $this->client->requestGet($endpoint.'?'.$queryString);

            $newDataSet = $this->getLastResponseAsArray();

            $returnData = array_merge_recursive($returnData, $newDataSet);

            $offset += $_limit;

            if (empty($newDataSet) || !isset($newDataSet['limit']) || (
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
     * @param SimpleXMLElement $xml    XML Element the custom fields are attached to
     * @param array            $fields array of fields to attach, each field needs name, id and value set
     *
     * @return SimpleXMLElement $xml
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_api#Working-with-custom-fields
     */
    protected function attachCustomFieldXML(SimpleXMLElement $xml, array $fields)
    {
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

    private function getLastResponseAsArray(): array
    {
        $body = $this->client->getLastResponseBody();

        // if body is empty
        if ($body === '') {
            return [
                'response' => '',
            ];
        }

        $contentType = $this->client->getLastResponseContentType();

        // parse XML
        if (0 === strpos($contentType, 'application/xml')) {
            $returnData = new SimpleXMLElement($body);

            try {
                $returnData = json_decode(
                    json_encode($returnData, \JSON_THROW_ON_ERROR),
                    true,
                    512,
                    \JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                throw new ClientException(
                    'Error decoding body as JSON: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        } else if (0 === strpos($contentType, 'application/json')) {
            try {
                $returnData = json_decode(
                    $body,
                    true,
                    512,
                    \JSON_THROW_ON_ERROR
                );
            } catch (JsonException $e) {
                throw new ClientException(
                    'Error decoding body as JSON: ' . $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }
        } else {
            $returnData = [
                'response' => $body,
            ];
        }

        return $returnData;
    }
}

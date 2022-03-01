<?php

namespace Redmine\Api;

use Redmine\Api;
use Redmine\Client\Client;
use Redmine\Exception\SerializerException;
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
                return JsonSerializer::createFromString($body)->getNormalized();
            } catch (SerializerException $e) {
                return 'Error decoding body as JSON: '.$e->getPrevious()->getMessage();
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
     * @deprecated the `retrieveAll()` method is deprecated, use `retrieveData()` instead
     *
     * @param string $endpoint API end point
     * @param array  $params   optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|false elements found
     */
    protected function retrieveAll($endpoint, array $params = [])
    {
        @trigger_error('The '.__METHOD__.' method is deprecated, use `retrieveData()` instead.', E_USER_DEPRECATED);

        try {
            $data = $this->retrieveData(strval($endpoint), $params);
        } catch (SerializerException $e) {
            $data = false;
        }

        return $data;
    }

    /**
     * Retrieves all the elements of a given endpoint (even if the
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
            $this->client->requestGet($endpoint);

            return $this->getLastResponseBodyAsArray();
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

            $this->client->requestGet(
                PathSerializer::create($endpoint, $params)->getPath()
            );

            $newDataSet = $this->getLastResponseBodyAsArray();

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

    /**
     * returns the last response body as array.
     *
     * @throws SerializerException if response body could not be converted into array
     */
    private function getLastResponseBodyAsArray(): array
    {
        $body = $this->client->getLastResponseBody();
        $contentType = $this->client->getLastResponseContentType();
        $returnData = null;

        // parse XML
        if (0 === strpos($contentType, 'application/xml')) {
            $returnData = XmlSerializer::createFromString($body)->getNormalized();
        } elseif (0 === strpos($contentType, 'application/json')) {
            $returnData = JsonSerializer::createFromString($body)->getNormalized();
        }

        if (!is_array($returnData)) {
            throw new SerializerException('Could not convert response body into array: '.$body);
        }

        return $returnData;
    }
}

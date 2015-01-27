<?php

namespace Redmine\Api;

use Redmine\Client;

/**
 * Abstract class for Api classes
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
abstract class AbstractApi
{
    /**
     * The client
     *
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Returns whether or not the last api call failed.
     * @return bool
     */
    public function lastCallFailed()
    {
        $code = $this->client->getResponseCode();

        return 200 !== $code && 201 !== $code;
    }

    /**
     * Perform the client get() method.
     *
     * @param string $path
     *
     * @return array
     */
    protected function get($path)
    {
        return $this->client->get($path);
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
        return $this->client->post($path, $data);
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
        return $this->client->put($path, $data);
    }

    /**
     * Perform the client delete() method.
     *
     * @param string $path
     *
     * @return array
     */
    protected function delete($path)
    {
        return $this->client->delete($path);
    }

    /**
     * Checks if the variable passed is not null
     *
     * @param mixed $var Variable to be checked
     *
     * @return bool
     */
    protected function isNotNull($var)
    {
        return !is_null($var);
    }

    /**
     * Retrieves all the elements of a given endpoint (even if the
     * total number of elements is greater than 100)
     *
     * @param  string $endpoint API end point
     * @param  array  $params   optional parameters to be passed to the api (offset, limit, ...)
     * @return array  elements found
     */
    protected function retrieveAll($endpoint, array $params = array())
    {
        if (empty($params)) {
            return $this->get($endpoint);
        }
        $defaults = array(
            'limit'  => 25,
            'offset' => 0,
        );
        $params = array_filter(
            array_merge($defaults, $params),
            array($this, 'isNotNull')
        );

        $ret = array();

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

            $newDataSet = (array) $this->get($endpoint . '?' . http_build_query($params));
            $ret = array_merge_recursive($ret, $newDataSet);

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

        return $ret;
    }

    /**
     * Attaches Custom Fields to a create/update query
     *
     * @param  SimpleXMLElement $xml    XML Element the custom fields are attached to
     * @param  array            $fields array of fields to attach, each field needs name, id and value set
     * @return SimpleXMLElement $xml
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
            $_field->addAttribute('id', $field['id']);
            if(is_array($field['value']))
            {
                $_subfield = $_field->addChild('value');
                $_subfield->addAttribute('type', 'array');
                foreach($field['value'] as $value)
                {
                    $_subfield->addChild('value', $value);
                }
            }
            else
                $_field->addChild('value', $field['value']);
        }

        return $xml;
    }
}

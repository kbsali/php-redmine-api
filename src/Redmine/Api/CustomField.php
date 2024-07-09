<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

/**
 * Listing custom fields.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class CustomField extends AbstractApi
{
    private $customFields = [];

    private $customFieldNames = null;

    /**
     * List custom fields.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of custom fields found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/custom_fields.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all custom fields with id/name pairs.
     *
     * @return array<int,string> list of custom fields (id => name)
     */
    final public function listNames(): array
    {
        if ($this->customFieldNames !== null) {
            return $this->customFieldNames;
        }

        $this->customFieldNames = [];

        $list = $this->list();

        if (array_key_exists('custom_fields', $list)) {
            foreach ($list['custom_fields'] as $customField) {
                $this->customFieldNames[(int) $customField['id']] = (string) $customField['name'];
            }
        }

        return $this->customFieldNames;
    }

    /**
     * List custom fields.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see CustomField::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of custom fields found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->customFields = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->customFields;
    }

    /**
     * Returns an array of custom fields with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see CustomField::listNames()
     *
     * @param bool  $forceUpdate to force the update of the custom fields var
     * @param array $params      optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of custom fields (id => name)
     */
    public function listing($forceUpdate = false, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        return $this->doListing($forceUpdate, $params);
    }

    /**
     * Get a custom field id given its name.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see CustomField::listNames()
     *
     * @param string|int $name   customer field name
     * @param array      $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return int|false
     */
    public function getIdByName($name, array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        $arr = $this->doListing(false, $params);

        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    private function doListing(bool $forceUpdate, array $params)
    {
        if (empty($this->customFields) || $forceUpdate) {
            $this->customFields = $this->list($params);
        }

        $ret = [];

        foreach ($this->customFields['custom_fields'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }
}

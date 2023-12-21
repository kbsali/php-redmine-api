<?php

namespace Redmine\Api;

use Redmine\Exception;

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

    /**
     * List custom fields.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of custom fields found
     */
    final public function list(array $params = []): array
    {
        $this->customFields = $this->retrieveData('/custom_fields.json', $params);

        return $this->customFields;
    }

    /**
     * List custom fields.
     *
     * @deprecated since v2.4.0, use list() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_CustomFields#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of custom fields found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::list()` instead.', E_USER_DEPRECATED);

        try {
            return $this->list($params);
        } catch (Exception $e) {
            if ($this->client->getLastResponseBody() === '') {
                return false;
            }

            return $e->getMessage();
        }
    }

    /**
     * Returns an array of custom fields with name/id pairs.
     *
     * @param bool  $forceUpdate to force the update of the custom fields var
     * @param array $params      optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of custom fields (id => name)
     */
    public function listing($forceUpdate = false, array $params = [])
    {
        if (empty($this->customFields) || $forceUpdate) {
            $this->list($params);
        }
        $ret = [];
        foreach ($this->customFields['custom_fields'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a tracket id given its name.
     *
     * @param string|int $name   customer field name
     * @param array      $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return int|false
     */
    public function getIdByName($name, array $params = [])
    {
        $arr = $this->listing(false, $params);
        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }
}

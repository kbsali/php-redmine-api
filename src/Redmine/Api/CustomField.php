<?php

namespace Redmine\Api;

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
    public function all(array $params = [])
    {
        $this->customFields = $this->retrieveAll('/custom_fields.json', $params);

        return $this->customFields;
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
            $this->all($params);
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

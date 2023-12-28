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
            $this->customFields = $this->retrieveData('/custom_fields.json', $params);
        } catch (SerializerException $th) {
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }

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
            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

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

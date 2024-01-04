<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

/**
 * Listing issue statuses.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_IssueStatuses
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class IssueStatus extends AbstractApi
{
    private $issueStatuses = [];

    /**
     * List issue statuses.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueStatuses#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of issue statuses found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/issue_statuses.json', $params);
        } catch (SerializerException $th) {
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }
    }

    /**
     * List issue statuses.
     *
     * @deprecated since v2.4.0, use list() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueStatuses#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of issue statuses found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->issueStatuses = $this->list($params);
        } catch (Exception $e) {
            if ($this->client->getLastResponseBody() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->issueStatuses;
    }

    /**
     * Returns an array of issue statuses with name/id pairs.
     *
     * @param bool $forceUpdate to force the update of the statuses var
     *
     * @return array list of issue statuses (id => name)
     */
    public function listing($forceUpdate = false)
    {
        if (empty($this->issueStatuses) || $forceUpdate) {
            $this->issueStatuses = $this->list();
        }
        $ret = [];
        foreach ($this->issueStatuses['issue_statuses'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a status id given its name.
     *
     * @param string $name
     *
     * @return int|false
     */
    public function getIdByName($name)
    {
        $arr = $this->listing();
        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }
}

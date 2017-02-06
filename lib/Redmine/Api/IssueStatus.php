<?php

namespace Redmine\Api;

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
     * @return array list of issue statuses found
     */
    public function all(array $params = [])
    {
        $this->issueStatuses = $this->retrieveAll('/issue_statuses.json', $params);

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
            $this->all();
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

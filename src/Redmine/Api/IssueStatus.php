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

    private $issueStatusNames = null;

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
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all issue statuses with id/name pairs.
     *
     * @return array<int,string> list of issue statuses (id => name)
     */
    final public function listNames(): array
    {
        if ($this->issueStatusNames !== null) {
            return $this->issueStatusNames;
        }

        $this->issueStatusNames = [];

        $list = $this->list();

        if (array_key_exists('issue_statuses', $list)) {
            foreach ($list['issue_statuses'] as $issueStatus) {
                $this->issueStatusNames[(int) $issueStatus['id']] = (string) $issueStatus['name'];
            }
        }

        return $this->issueStatusNames;
    }

    /**
     * List issue statuses.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see IssueStatus::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_IssueStatuses#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of issue statuses found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->issueStatuses = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
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
     * @deprecated v2.7.0 Use listNames() instead.
     * @see IssueStatus::listNames()
     *
     * @param bool $forceUpdate to force the update of the statuses var
     *
     * @return array list of issue statuses (id => name)
     */
    public function listing($forceUpdate = false)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        return $this->doListing($forceUpdate);
    }

    /**
     * Get a status id given its name.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see IssueStatus::listNames()
     *
     * @param string $name
     *
     * @return int|false
     */
    public function getIdByName($name)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        $arr = $this->doListing(false);

        if (!isset($arr[$name])) {
            return false;
        }

        return $arr[(string) $name];
    }

    private function doListing(bool $forceUpdate)
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
}

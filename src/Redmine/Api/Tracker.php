<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

/**
 * Listing trackers.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Trackers
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Tracker extends AbstractApi
{
    private $trackers = [];

    private $trackerNames = null;

    /**
     * List trackers.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Trackers#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of trackers found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/trackers.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all trackers with id/name pairs.
     *
     * @return array<int,string> list of trackers (id => name)
     */
    final public function listNames(): array
    {
        if ($this->trackerNames !== null) {
            return $this->trackerNames;
        }

        $this->trackerNames = [];
        $list = $this->list();

        if (array_key_exists('trackers', $list)) {
            foreach ($list['trackers'] as $role) {
                $this->trackerNames[(int) $role['id']] = $role['name'];
            }
        }

        return $this->trackerNames;
    }

    /**
     * List trackers.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see Tracker::list()
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Trackers#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of trackers found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->trackers = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->trackers;
    }

    /**
     * Returns an array of trackers with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see Tracker::listNames()
     *
     * @param bool $forceUpdate to force the update of the trackers var
     *
     * @return array list of trackers (id => name)
     */
    public function listing($forceUpdate = false)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        return $this->doListing($forceUpdate);
    }

    /**
     * Get a tracker id given its name.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see Tracker::listNames()
     *
     * @param string|int $name tracker name
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
        if (empty($this->trackers) || $forceUpdate) {
            $this->trackers = $this->list();
        }

        $ret = [];

        foreach ($this->trackers['trackers'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }
}

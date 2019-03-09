<?php

namespace Redmine\Api;

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

    /**
     * List trackers.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Trackers#GET
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of trackers found
     */
    public function all(array $params = [])
    {
        $this->trackers = $this->retrieveAll('/trackers.json', $params);

        return $this->trackers;
    }

    /**
     * Returns an array of trackers with name/id pairs.
     *
     * @param bool $forceUpdate to force the update of the trackers var
     *
     * @return array list of trackers (id => name)
     */
    public function listing($forceUpdate = false)
    {
        if (empty($this->trackers) || $forceUpdate) {
            $this->all();
        }
        $ret = [];
        foreach ($this->trackers['trackers'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a tracket id given its name.
     *
     * @param string|int $name tracker name
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

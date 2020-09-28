<?php

namespace Redmine\Api;

/**
 * Listing time entry activities.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationstime_entry_activitiesformat
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class TimeEntryActivity extends AbstractApi
{
    private $timeEntryActivities = [];

    /**
     * List time entry activities.
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of time entry activities found
     */
    public function all(array $params = [])
    {
        $this->timeEntryActivities = $this->retrieveAll('/enumerations/time_entry_activities.json', $params);

        return $this->timeEntryActivities;
    }

    /**
     * Returns an array of time entry activities with name/id pairs.
     *
     * @param bool $forceUpdate to force the update of the statuses var
     *
     * @return array list of time entry activities (id => name)
     */
    public function listing($forceUpdate = false)
    {
        if (empty($this->timeEntryActivities) || $forceUpdate) {
            $this->all();
        }
        $ret = [];
        foreach ($this->timeEntryActivities['time_entry_activities'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }

    /**
     * Get a activities id given its name.
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

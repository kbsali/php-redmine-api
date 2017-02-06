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
}

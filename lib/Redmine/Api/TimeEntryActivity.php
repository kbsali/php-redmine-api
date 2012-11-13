<?php

namespace Redmine\Api;

/**
 * Listing time entry activities
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationstime_entry_activitiesformat
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class TimeEntryActivity extends AbstractApi
{
    private $timeEntryActivities = array();

    /**
     * List time entry activities
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Enumerations#enumerationstime_entry_activitiesformat
     *
     * @return array list of time entry activities found
     */
    public function all()
    {
        $this->timeEntryActivities = $this->get('/enumerations/time_entry_activities.json');

        return $this->timeEntryActivities;
    }
}

<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;

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

    private $timeEntryActivityNames = null;

    /**
     * List time entry activities.
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of time entry activities found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/enumerations/time_entry_activities.json', $params);
        } catch (SerializerException $th) {
            throw UnexpectedResponseException::create($this->getLastResponse(), $th);
        }
    }

    /**
     * Returns an array of all time entry activities with id/name pairs.
     *
     * @return array<int,string> list of time entry activities (id => name)
     */
    final public function listNames(): array
    {
        if ($this->timeEntryActivityNames !== null) {
            return $this->timeEntryActivityNames;
        }

        $this->timeEntryActivityNames = [];
        $list = $this->list();

        if (array_key_exists('time_entry_activities', $list)) {
            foreach ($list['time_entry_activities'] as $activity) {
                $this->timeEntryActivityNames[(int) $activity['id']] = $activity['name'];
            }
        }

        return $this->timeEntryActivityNames;
    }

    /**
     * List time entry activities.
     *
     * @deprecated v2.4.0 Use list() instead.
     * @see TimeEntryActivity::list()
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of time entry activities found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.4.0, use `' . __CLASS__ . '::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->timeEntryActivities = $this->list($params);
        } catch (Exception $e) {
            if ($this->getLastResponse()->getContent() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->timeEntryActivities;
    }

    /**
     * Returns an array of time entry activities with name/id pairs.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see TimeEntryActivity::listNames()
     *
     * @param bool $forceUpdate to force the update of the statuses var
     *
     * @return array list of time entry activities (id => name)
     */
    public function listing($forceUpdate = false)
    {
        @trigger_error('`' . __METHOD__ . '()` is deprecated since v2.7.0, use `' . __CLASS__ . '::listNames()` instead.', E_USER_DEPRECATED);

        return $this->doListing((bool) $forceUpdate);
    }

    /**
     * Get a activities id given its name.
     *
     * @deprecated v2.7.0 Use listNames() instead.
     * @see Project::listNames()
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
        if (empty($this->timeEntryActivities) || $forceUpdate) {
            $this->timeEntryActivities = $this->list();
        }

        $ret = [];

        foreach ($this->timeEntryActivities['time_entry_activities'] as $e) {
            $ret[$e['name']] = (int) $e['id'];
        }

        return $ret;
    }
}

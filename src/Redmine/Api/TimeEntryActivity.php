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
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }
    }

    /**
     * List time entry activities.
     *
     * @deprecated since v2.4.0, use list() instead.
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
            if ($this->getLastResonse()->getBody() === '') {
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
     * @param bool $forceUpdate to force the update of the statuses var
     *
     * @return array list of time entry activities (id => name)
     */
    public function listing($forceUpdate = false)
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

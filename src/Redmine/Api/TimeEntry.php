<?php

namespace Redmine\Api;

use Redmine\Exception;
use Redmine\Exception\MissingParameterException;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Serializer\XmlSerializer;

/**
 * Listing time entries, creating, editing.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class TimeEntry extends AbstractApi
{
    private $timeEntries = [];

    /**
     * List time entries.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @throws UnexpectedResponseException if response body could not be converted into array
     *
     * @return array list of time entries found
     */
    final public function list(array $params = []): array
    {
        try {
            return $this->retrieveData('/time_entries.json', $params);
        } catch (SerializerException $th) {
            throw new UnexpectedResponseException('The Redmine server responded with an unexpected body.', $th->getCode(), $th);
        }
    }

    /**
     * List time entries.
     *
     * @deprecated since v2.4.0, use list() instead.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array|string|false list of time entries found or error message or false
     */
    public function all(array $params = [])
    {
        @trigger_error('`'.__METHOD__.'()` is deprecated since v2.4.0, use `'.__CLASS__.'::list()` instead.', E_USER_DEPRECATED);

        try {
            $this->timeEntries = $this->list($params);
        } catch (Exception $e) {
            if ($this->client->getLastResponseBody() === '') {
                return false;
            }

            if ($e instanceof UnexpectedResponseException && $e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            return $e->getMessage();
        }

        return $this->timeEntries;
    }

    /**
     * Get extended information about a time entry (including memberships + groups).
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param string $id the time entry id
     *
     * @return array information about the time entry
     */
    public function show($id)
    {
        return $this->get('/time_entries/'.urlencode($id).'.json');
    }

    /**
     * Create a new time entry given an array of $params.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params the new time entry data
     *
     * @throws MissingParameterException Missing mandatory parameters
     *
     * @return string|false
     */
    public function create(array $params = [])
    {
        $defaults = [
            'issue_id' => null,
            'project_id' => null,
            'spent_on' => null,
            'hours' => null,
            'activity_id' => null,
            'comments' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        if (
            (!isset($params['issue_id']) && !isset($params['project_id']))
         || !isset($params['hours'])
        ) {
            throw new MissingParameterException('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');
        }

        return $this->post(
            '/time_entries.xml',
            XmlSerializer::createFromArray(['time_entry' => $params])->getEncoded()
        );
    }

    /**
     * Update time entry's information.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int $id
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'id' => $id,
            'issue_id' => null,
            'project_id' => null,
            'spent_on' => null,
            'hours' => null,
            'activity_id' => null,
            'comments' => null,
        ];
        $params = $this->sanitizeParams($defaults, $params);

        return $this->put(
            '/time_entries/'.$id.'.xml',
            XmlSerializer::createFromArray(['time_entry' => $params])->getEncoded()
        );
    }

    /**
     * Delete a time entry.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int $id id of the time entry
     *
     * @return false|\SimpleXMLElement|string
     */
    public function remove($id)
    {
        return $this->delete('/time_entries/'.$id.'.xml');
    }
}

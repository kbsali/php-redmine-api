<?php

namespace Redmine\Api;

use Redmine\Exception\MissingParameterException;

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
     * @return array list of time entries found
     */
    public function all(array $params = [])
    {
        $this->timeEntries = $this->retrieveData('/time_entries.json', $params);

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

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><time_entry></time_entry>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k && is_array($v)) {
                $this->attachCustomFieldXML($xml, $v);
            } else {
                $xml->addChild($k, htmlspecialchars($v));
            }
        }

        return $this->post('/time_entries.xml', $xml->asXML());
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

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><time_entry></time_entry>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k && is_array($v)) {
                $this->attachCustomFieldXML($xml, $v);
            } else {
                $xml->addChild($k, htmlspecialchars($v));
            }
        }

        return $this->put('/time_entries/'.$id.'.xml', $xml->asXML());
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

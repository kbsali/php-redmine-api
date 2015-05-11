<?php

namespace Redmine\Api;

/**
 * Listing time entries, creating, editing.
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class TimeEntry extends AbstractApi
{
    private $timeEntries = array();

    /**
     * List time entries.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params optional parameters to be passed to the api (offset, limit, ...)
     *
     * @return array list of time entries found
     */
    public function all(array $params = array())
    {
        $this->timeEntries = $this->retrieveAll('/time_entries.json', $params);

        return $this->timeEntries;
    }

    /**
     * Get extended information about a time entry (including memberships + groups).
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
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
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param array $params the new time entry data
     *
     * @return SimpleXMLElement
     */
    public function create(array $params = array())
    {
        $defaults = array(
            'issue_id' => null,
            'project_id' => null,
            'spent_on' => null,
            'hours' => null,
            'activity_id' => null,
            'comments' => null,
        );
        $params = $this->sanitizeParams($defaults, $params);

        if (
            (!isset($params['issue_id']) && !isset($params['project_id']))
         || !isset($params['hours'])
        ) {
            throw new \Exception('Missing mandatory parameters');
        }

        $xml = new SimpleXMLElement('<?xml version="1.0"?><time_entry></time_entry>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k && is_array($v)) {
                $this->attachCustomFieldXML($xml, $v);
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $this->post('/time_entries.xml', $xml->asXML());
    }

    /**
     * Update time entry's informations.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param id    $id
     * @param array $params
     *
     * @return SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = array(
            'id' => $id,
            'issue_id' => null,
            'project_id' => null,
            'spent_on' => null,
            'hours' => null,
            'activity_id' => null,
            'comments' => null,
        );
        $params = $this->sanitizeParams($defaults, $params);

        $xml = new SimpleXMLElement('<?xml version="1.0"?><time_entry></time_entry>');
        foreach ($params as $k => $v) {
            if ('custom_fields' === $k && is_array($v)) {
                $this->attachCustomFieldXML($xml, $v);
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $this->put('/time_entries/'.$id.'.xml', $xml->asXML());
    }

    /**
     * Delete a time entry.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_TimeEntries
     *
     * @param int $id id of the time entry
     */
    public function remove($id)
    {
        return $this->delete('/time_entries/'.$id.'.xml');
    }
}

<?php

namespace Redmine\Api;

/**
 * Listing issues, searching, editing and closing your projects issues.
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Issues
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Issue extends AbstractApi
{
    const PRIO_LOW       = 1;
    const PRIO_NORMAL    = 2;
    const PRIO_HIGH      = 3;
    const PRIO_URGENT    = 4;
    const PRIO_IMMEDIATE = 5;

    /**
     * List issues
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues
     * available $params :
     * - offset: skip this number of issues in response (optional)
     * - limit: number of issues per page (optional)
     * - sort: column to sort with. Append :desc to invert the order.
     * - project_id: get issues from the project with the given id, where id is either project id or project identifier
     * - tracker_id: get issues from the tracker with the given id
     * - status_id: get issues with the given status id only. Possible values: open, closed, * to get open and closed issues, status id
     * - assigned_to_id: get issues which are assigned to the given user id
     * - cf_x: get issues with the given value for custom field with an ID of x. (Custom field must have 'used as a filter' checked.)
     * - query_id : id of the previously saved query
     *
     * @param  array $params the additional parameters (cf avaiable $params above)
     * @return array list of issues found
     */
    public function all(array $params = array())
    {
        return $this->get('/issues.json?'.$this->http_build_str($params));
    }

    /**
     * Get extended information about an issue gitven its id
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Using-JSON
     * available $params :
     * include: fetch associated data (optional). Possible values: children, attachments, relations, changesets and journals
     *
     * @param  string $id     the issue id
     * @param  array  $params extra associated data
     * @return array  information about the issue
     */
    public function show($id, array $params = array())
    {
        return $this->get('/issues/'.urlencode($id).'.json?'.$this->http_build_str($params));
    }

    /**
     * Create a new issue given an array of $params
     * The issue is assigned to the authenticated user.
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Creating-an-issue
     *
     * @param  array             $params the new issue data
     * @return \SimpleXMLElement
     */
    public function create(array $params = array())
    {
        $defaults = array(
            'subject'        => null,
            'description'    => null,

            // 'project'     => null,
            // 'category'    => null,
            // 'status'      => null,
            // 'tracker'     => null,
            // 'assigned_to' => null,
            // 'author'      => null,

            'project_id'     => null,
            'category_id'    => null,
            'priority_id'    => null,
            'status_id'      => null,
            'tracker_id'     => null,
            'assigned_to_id' => null,
            'author_id'      => null,
            'due_date'       => null,
            'start_date'     => null,
        );
        $params = $this->cleanParams($params);
        $params = array_filter(array_merge($defaults, $params));

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->post('/issues.xml', $xml->asXML());
        // $json = json_encode(array('issue' => $params));
        // return $this->post('/issues.json', $json);
    }

    /**
     * Update issue information's by username, repo and issue number. Requires authentication.
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param  string            $id     the issue number
     * @param  array             $params
     * @return \SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = array(
            'id'             => $id,
            'subject'        => null,
            'notes'          => null,

            // 'project'     => null,
            // 'category'    => null,
            // 'status'      => null,
            // 'tracker'     => null,
            // 'assigned_to' => null,
            // 'author'      => null,

            'category_id'    => null,
            'priority_id'    => null,
            'status_id'      => null,
            'tracker_id'     => null,
            'assigned_to_id' => null,
            'due_date'       => null,
        );
        $params = $this->cleanParams($params);
        $params = array_filter(array_merge($defaults, $params));

        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');
        foreach ($params as $k => $v) {
            $xml->addChild($k, $v);
        }

        return $this->put('/issues/'.$id.'.xml', $xml->asXML());
    }

    /**
     * @param  int    $id
     * @param  string $status
     * @return void
     */
    public function setIssueStatus($id, $status)
    {
        $statusId = $this->client->api('issue_status')->getIdByName($status);

        return $this->update($id, array(
            'status_id' => $statusId
        ));
    }

    /**
     * @param  int    $id
     * @param  string $note
     * @return void
     */
    public function addNoteToIssue($id, $note)
    {
        return $this->update($id, array(
            'notes' => $note
        ));
    }

    /**
     * Transforms literal identifiers to integer ids
     * @param  array $params
     * @return array
     */
    private function cleanParams(array $params = array())
    {
        if (isset($params['project'])) {
            $params['project_id'] = $this->client->api('project')->getIdByName($params['project']);
            unset($params['project']);

            if (isset($params['category'])) {
                $params['category_id'] = $this->client->api('issue_category')->getIdByName($params['project_id'], $params['project']);
                unset($params['category']);
            }
        }
        if (isset($params['status'])) {
            $params['status_id'] = $this->client->api('issue_status')->getIdByName($params['status']);
            unset($params['status']);
        }
        if (isset($params['tracker'])) {
            $params['tracker_id'] = $this->client->api('tracker')->getIdByName($params['tracker']);
            unset($params['tracker']);
        }
        if (isset($params['assigned_to'])) {
            $params['assigned_to'] = $this->client->api('user')->getIdByUsername($params['assigned_to']);
            unset($params['assigned_to']);
        }
        if (isset($params['author'])) {
            $params['author_id'] = $this->client->api('user')->getIdByUsername($params['author']);
            unset($params['author']);
        }

        return $params;
    }

    /**
     * Attach a file to an issue issue number. Requires authentication.
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param  string            $id     the issue number
     * @param  array             $attachment
     * @return bool|string
     */
    public function attach($id, array $attachment)
    {
        $request['issue'] = array('id' => $id, 'uploads' => array('upload' => $attachment));
        return $this->put('/issues/'.$id.'.json', json_encode($request));
    }
}

<?php

namespace Redmine\Api;

/**
 * Listing issues, searching, editing and closing your projects issues.
 *
 * @link   http://www.redmine.org/projects/redmine/wiki/Rest_Issues
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Issue extends AbstractApi
{
    const PRIO_LOW = 1;
    const PRIO_NORMAL = 2;
    const PRIO_HIGH = 3;
    const PRIO_URGENT = 4;
    const PRIO_IMMEDIATE = 5;

    /**
     * List issues.
     *
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
     * @param array $params the additional parameters (cf avaiable $params above)
     *
     * @return array list of issues found
     */
    public function all(array $params = array())
    {
        return $this->retrieveAll('/issues.json', $params);
    }

    /**
     * Get extended information about an issue gitven its id.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Using-JSON
     * available $params :
     * include: fetch associated data (optional). Possible values: children, attachments, relations, changesets and journals
     *
     * @param string $id     the issue id
     * @param array  $params extra associated data
     *
     * @return array information about the issue
     */
    public function show($id, array $params = array())
    {
        if (isset($params['include']) && is_array($params['include'])) {
            $params['include'] = implode(',', $params['include']);
        }

        return $this->get('/issues/'.urlencode($id).'.json?'.http_build_query($params));
    }

    /**
     * Build the XML for an issue.
     *
     * @param array $params for the new/updated issue data
     *
     * @return SimpleXMLElement
     */
    private function buildXML(array $params = array())
    {
        $xml = new SimpleXMLElement('<?xml version="1.0"?><issue></issue>');

        foreach ($params as $k => $v) {
            if ('custom_fields' === $k && is_array($v)) {
                $this->attachCustomFieldXML($xml, $v);
            } elseif ('watcher_user_ids' === $k && is_array($v)) {
                $watcherUserIds = $xml->addChild('watcher_user_ids', '');
                $watcherUserIds->addAttribute('type', 'array');
                foreach ($v as $watcher) {
                    $watcherUserIds->addChild('watcher_user_id', (int) $watcher);
                }
            } elseif ('uploads' === $k && is_array($v)) {
                $uploadsItem = $xml->addChild('uploads', '');
                $uploadsItem->addAttribute('type', 'array');
                foreach ($v as $upload) {
                    $upload_item = $uploadsItem->addChild('upload', '');
                    foreach ($upload as $upload_k => $upload_v) {
                        $upload_item->addChild($upload_k, $upload_v);
                    }
                }
            } elseif ('description' === $k && strpos($v, '\n') !== false) {
                // surround the description with CDATA if there is any '\n' in the description
                $node = $xml->addChild($k);
                $domNode = dom_import_simplexml($node);
                $no = $domNode->ownerDocument;
                $domNode->appendChild($no->createCDATASection($v));
            } else {
                $xml->addChild($k, $v);
            }
        }

        return $xml;
    }

    /**
     * Create a new issue given an array of $params
     * The issue is assigned to the authenticated user.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Creating-an-issue
     *
     * @param array $params the new issue data
     *
     * @return SimpleXMLElement
     */
    public function create(array $params = array())
    {
        $defaults = array(
            'subject' => null,
            'description' => null,

            // 'project' => null,
            // 'category' => null,
            // 'status' => null,
            // 'tracker' => null,
            // 'assigned_to' => null,
            // 'author' => null,

            'project_id' => null,
            'category_id' => null,
            'priority_id' => null,
            'status_id' => null,
            'tracker_id' => null,
            'assigned_to_id' => null,
            'author_id' => null,
            'due_date' => null,
            'start_date' => null,
            'watcher_user_ids' => null,
            'fixed_version_id' => null,
        );
        $params = $this->cleanParams($params);
        $params = $this->sanitizeParams($defaults, $params);

        $xml = $this->buildXML($params);

        return $this->post('/issues.xml', $xml->asXML());
        // $json = json_encode(array('issue' => $params));
        // return $this->post('/issues.json', $json);
    }

    /**
     * Update issue information's by username, repo and issue number. Requires authentication.
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param string $id     the issue number
     * @param array  $params
     *
     * @return SimpleXMLElement
     */
    public function update($id, array $params)
    {
        $defaults = array(
            'id' => $id,
            'subject' => null,
            'notes' => null,
            'private_notes' => false,

            // 'project' => null,
            // 'category' => null,
            // 'status' => null,
            // 'tracker' => null,
            // 'assigned_to' => null,
            // 'author' => null,

            'category_id' => null,
            'priority_id' => null,
            'status_id' => null,
            'tracker_id' => null,
            'assigned_to_id' => null,
            'due_date' => null,
        );
        $params = $this->cleanParams($params);
        $params = $this->sanitizeParams($defaults, $params);

        $xml = $this->buildXML($params);

        return $this->put('/issues/'.$id.'.xml', $xml->asXML());
    }

    /**
     * @param int    $id
     * @param string $watcher_user_id
     */
    public function addWatcher($id, $watcher_user_id)
    {
        return $this->post('/issues/'.$id.'/watchers.xml', '<user_id>'.$watcher_user_id.'</user_id>');
    }

    /**
     * @param int    $id
     * @param string $watcher_user_id
     */
    public function removeWatcher($id, $watcher_user_id)
    {
        return $this->delete('/issues/'.$id.'/watchers/'.$watcher_user_id.'.xml');
    }

    /**
     * @param int    $id
     * @param string $status
     *
     * @return SimpleXMLElement
     */
    public function setIssueStatus($id, $status)
    {
        $statusId = $this->client->api('issue_status')->getIdByName($status);

        return $this->update($id, array(
            'status_id' => $statusId,
        ));
    }

    /**
     * @param int    $id
     * @param string $note
     * @param bool   $privateNote
     *
     * @return SimpleXMLElement
     */
    public function addNoteToIssue($id, $note, $privateNote = false)
    {
        return $this->update($id, array(
            'notes' => $note,
            'private_notes' => $privateNote,
        ));
    }

    /**
     * Transforms literal identifiers to integer ids.
     *
     * @param array $params
     *
     * @return array
     */
    private function cleanParams(array $params = array())
    {
        if (isset($params['project'])) {
            $params['project_id'] = $this->client->api('project')->getIdByName($params['project']);
            unset($params['project']);

            if (isset($params['category'])) {
                $params['category_id'] = $this->client->api('issue_category')->getIdByName($params['project_id'], $params['category']);
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
            $params['assigned_to_id'] = $this->client->api('user')->getIdByUsername($params['assigned_to']);
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
     *
     * @link http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param string $id         the issue number
     * @param array  $attachment
     *
     * @return bool|string
     */
    public function attach($id, array $attachment)
    {
        $request = array();
        $request['issue'] = array(
            'id' => $id,
            'uploads' => array(
                'upload' => $attachment,
            ),
        );

        return $this->put('/issues/'.$id.'.json', json_encode($request));
    }

    /**
     * Remove a issue by issue number.
     *
     * @param string $id the issue number
     */
    public function remove($id)
    {
        return $this->delete('/issues/'.$id.'.xml');
    }
}

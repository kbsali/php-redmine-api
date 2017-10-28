<?php

namespace Redmine\Api;

/**
 * Listing issues, searching, editing and closing your projects issues.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Issues
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
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues
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
     * @param array $params the additional parameters (cf available $params above)
     *
     * @return array list of issues found
     */
    public function all(array $params = [])
    {
        return $this->retrieveAll('/issues.json', $params);
    }

    /**
     * Get extended information about an issue gitven its id.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Using-JSON
     * available $params :
     * include: fetch associated data (optional). Possible values: children, attachments, relations, changesets and journals
     *
     * @param string $id     the issue id
     * @param array  $params extra associated data
     *
     * @return array information about the issue
     */
    public function show($id, array $params = [])
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
     * @return \SimpleXMLElement
     */
    private function buildXML(array $params = [])
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');

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
            } else {
                // "addChild" does not escape text for XML value, but the setter does.
                // http://stackoverflow.com/a/555039/99904
                $xml->$k = $v;
            }
        }

        return $xml;
    }

    /**
     * Create a new issue given an array of $params
     * The issue is assigned to the authenticated user.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Creating-an-issue
     *
     * @param array $params the new issue data
     *
     * @return \SimpleXMLElement
     */
    public function create(array $params = [])
    {
        $defaults = [
            'subject' => null,
            'description' => null,
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
        ];
        $params = $this->cleanParams($params);
        $params = $this->sanitizeParams($defaults, $params);

        $xml = $this->buildXML($params);

        return $this->post('/issues.xml', $xml->asXML());
    }

    /**
     * Update issue information's by username, repo and issue number. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param string $id     the issue number
     * @param array  $params
     *
     * @return string|false
     */
    public function update($id, array $params)
    {
        $defaults = [
            'id' => $id,
            'subject' => null,
            'notes' => null,
            'private_notes' => false,
            'category_id' => null,
            'priority_id' => null,
            'status_id' => null,
            'tracker_id' => null,
            'assigned_to_id' => null,
            'due_date' => null,
        ];
        $params = $this->cleanParams($params);
        $params = $this->sanitizeParams($defaults, $params);

        $xml = $this->buildXML($params);

        return $this->put('/issues/'.$id.'.xml', $xml->asXML());
    }

    /**
     * @param int    $id
     * @param string $watcher_user_id
     *
     * @return false|string
     */
    public function addWatcher($id, $watcher_user_id)
    {
        return $this->post('/issues/'.$id.'/watchers.xml', '<user_id>'.$watcher_user_id.'</user_id>');
    }

    /**
     * @param int    $id
     * @param string $watcher_user_id
     *
     * @return false|\SimpleXMLElement|string
     */
    public function removeWatcher($id, $watcher_user_id)
    {
        return $this->delete('/issues/'.$id.'/watchers/'.$watcher_user_id.'.xml');
    }

    /**
     * @param int    $id
     * @param string $status
     *
     * @return string|false
     */
    public function setIssueStatus($id, $status)
    {
        $api = $this->client->issue_status;
        $statusId = $api->getIdByName($status);

        return $this->update($id, [
            'status_id' => $statusId,
        ]);
    }

    /**
     * @param int    $id
     * @param string $note
     * @param bool   $privateNote
     *
     * @return string|false
     */
    public function addNoteToIssue($id, $note, $privateNote = false)
    {
        return $this->update($id, [
            'notes' => $note,
            'private_notes' => $privateNote,
        ]);
    }

    /**
     * Transforms literal identifiers to integer ids.
     *
     * @param array $params
     *
     * @return array
     */
    private function cleanParams(array $params = [])
    {
        if (isset($params['project'])) {
            $apiProject = $this->client->project;
            $params['project_id'] = $apiProject->getIdByName($params['project']);
            unset($params['project']);

            if (isset($params['category'])) {
                $apiIssueCategory = $this->client->issue_category;
                $params['category_id'] = $apiIssueCategory->getIdByName($params['project_id'], $params['category']);
                unset($params['category']);
            }
        }
        if (isset($params['status'])) {
            $apiIssueStatus = $this->client->issue_status;
            $params['status_id'] = $apiIssueStatus->getIdByName($params['status']);
            unset($params['status']);
        }
        if (isset($params['tracker'])) {
            $apiTracker = $this->client->tracker;
            $params['tracker_id'] = $apiTracker->getIdByName($params['tracker']);
            unset($params['tracker']);
        }
        if (isset($params['assigned_to'])) {
            $apiUser = $this->client->user;
            $params['assigned_to_id'] = $apiUser->getIdByUsername($params['assigned_to']);
            unset($params['assigned_to']);
        }
        if (isset($params['author'])) {
            $apiUser = $this->client->user;
            $params['author_id'] = $apiUser->getIdByUsername($params['author']);
            unset($params['author']);
        }

        return $params;
    }

    /**
     * Attach a file to an issue. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param string $id         the issue number
     * @param array  $attachment ['token' => '...', 'filename' => '...', 'content_type' => '...']
     *
     * @return bool|string
     */
    public function attach($id, array $attachment)
    {
        return $this->attachMany($id, [$attachment]);
    }

    /**
     * Attach files to an issue. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param string $id          the issue number
     * @param array  $attachments [
     *                            ['token' => '...', 'filename' => '...', 'content_type' => '...'],
     *                            ['token' => '...', 'filename' => '...', 'content_type' => '...']
     *                            ]
     *
     * @return bool|string
     */
    public function attachMany($id, array $attachments)
    {
        $request = [];
        $request['issue'] = [
            'id' => $id,
            'uploads' => $attachments,
        ];

        return $this->put('/issues/'.$id.'.json', json_encode($request));
    }

    /**
     * Remove a issue by issue number.
     *
     * @param string $id the issue number
     *
     * @return false|\SimpleXMLElement|string
     */
    public function remove($id)
    {
        return $this->delete('/issues/'.$id.'.xml');
    }
}

<?php

namespace Redmine\Api;

use Redmine\Serializer\JsonSerializer;
use Redmine\Serializer\PathSerializer;
use Redmine\Serializer\XmlSerializer;

/**
 * Listing issues, searching, editing and closing your projects issues.
 *
 * @see   http://www.redmine.org/projects/redmine/wiki/Rest_Issues
 *
 * @author Kevin Saliou <kevin at saliou dot name>
 */
class Issue extends AbstractApi
{
    public const PRIO_LOW = 1;
    public const PRIO_NORMAL = 2;
    public const PRIO_HIGH = 3;
    public const PRIO_URGENT = 4;
    public const PRIO_IMMEDIATE = 5;

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
        return $this->retrieveData('/issues.json', $params);
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

        return $this->get(
            PathSerializer::create('/issues/'.urlencode($id).'.json', $params)->getPath()
        );
    }

    /**
     * Create a new issue given an array of $params
     * The issue is assigned to the authenticated user.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Creating-an-issue
     *
     * @param array $params the new issue data
     *
     * @return string|false
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

        return $this->post(
            '/issues.xml',
            XmlSerializer::createFromArray(['issue' => $params])->getEncoded()
        );
    }

    /**
     * Update issue information's by username, repo and issue number. Requires authentication.
     *
     * @see http://www.redmine.org/projects/redmine/wiki/Rest_Issues#Updating-an-issue
     *
     * @param string $id the issue number
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
        $sanitizedParams = $this->sanitizeParams($defaults, $params);

        // Allow assigned_to_id to be `` (empty string) to unassign a user from an issue
        if (array_key_exists('assigned_to_id', $params) && '' === $params['assigned_to_id']) {
            $sanitizedParams['assigned_to_id'] = '';
        }

        return $this->put(
            '/issues/'.$id.'.xml',
            XmlSerializer::createFromArray(['issue' => $sanitizedParams])->getEncoded()
        );
    }

    /**
     * @param int    $id
     * @param string $watcherUserId
     *
     * @return false|string
     */
    public function addWatcher($id, $watcherUserId)
    {
        return $this->post(
            '/issues/'.$id.'/watchers.xml',
            XmlSerializer::createFromArray(['user_id' => $watcherUserId])->getEncoded()
        );
    }

    /**
     * @param int    $id
     * @param string $watcherUserId
     *
     * @return false|\SimpleXMLElement|string
     */
    public function removeWatcher($id, $watcherUserId)
    {
        return $this->delete('/issues/'.$id.'/watchers/'.$watcherUserId.'.xml');
    }

    /**
     * @param int    $id
     * @param string $status
     *
     * @return string|false
     */
    public function setIssueStatus($id, $status)
    {
        /** @var IssueStatus */
        $api = $this->client->getApi('issue_status');

        return $this->update($id, [
            'status_id' => $api->getIdByName($status),
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
     * @return array
     */
    private function cleanParams(array $params = [])
    {
        if (isset($params['project'])) {
            /** @var Project */
            $apiProject = $this->client->getApi('project');
            $params['project_id'] = $apiProject->getIdByName($params['project']);
            unset($params['project']);
            if (isset($params['category'])) {
                /** @var IssueCategory */
                $apiIssueCategory = $this->client->getApi('issue_category');
                $params['category_id'] = $apiIssueCategory->getIdByName($params['project_id'], $params['category']);
                unset($params['category']);
            }
        }
        if (isset($params['status'])) {
            /** @var IssueStatus */
            $apiIssueStatus = $this->client->getApi('issue_status');
            $params['status_id'] = $apiIssueStatus->getIdByName($params['status']);
            unset($params['status']);
        }
        if (isset($params['tracker'])) {
            /** @var Tracker */
            $apiTracker = $this->client->getApi('tracker');
            $params['tracker_id'] = $apiTracker->getIdByName($params['tracker']);
            unset($params['tracker']);
        }
        if (isset($params['assigned_to'])) {
            /** @var User */
            $apiUser = $this->client->getApi('user');
            $params['assigned_to_id'] = $apiUser->getIdByUsername($params['assigned_to']);
            unset($params['assigned_to']);
        }
        if (isset($params['author'])) {
            /** @var User */
            $apiUser = $this->client->getApi('user');
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
        $params = [
            'id' => $id,
            'uploads' => $attachments,
        ];

        return $this->put(
            '/issues/'.$id.'.json',
            JsonSerializer::createFromArray(['issue' => $params])->getEncoded()
        );
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

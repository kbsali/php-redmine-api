<?php

namespace Redmine;

use Redmine\Api\ApiInterface;

/**
 * client api trait.
 */
trait ClientApiTrait
{
    /**
     * @var array APIs
     */
    private $apiInstances = [];

    private $apiClassnames = [
        'attachment' => 'Attachment',
        'group' => 'Group',
        'custom_fields' => 'CustomField',
        'issue' => 'Issue',
        'issue_category' => 'IssueCategory',
        'issue_priority' => 'IssuePriority',
        'issue_relation' => 'IssueRelation',
        'issue_status' => 'IssueStatus',
        'membership' => 'Membership',
        'news' => 'News',
        'project' => 'Project',
        'query' => 'Query',
        'role' => 'Role',
        'time_entry' => 'TimeEntry',
        'time_entry_activity' => 'TimeEntryActivity',
        'tracker' => 'Tracker',
        'user' => 'User',
        'version' => 'Version',
        'wiki' => 'Wiki',
        'search' => 'Search',
    ];

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return ApiInterface
     */
    public function getApi(string $name): ApiInterface
    {
        if (!isset($this->apiClassnames[$name])) {
            throw new \InvalidArgumentException();
        }
        if (isset($this->apiInstances[$name])) {
            return $this->apiInstances[$name];
        }
        $class = 'Redmine\Api\\'.$this->apiClassnames[$name];
        $this->apiInstances[$name] = new $class($this);

        return $this->apiInstances[$name];
    }
}

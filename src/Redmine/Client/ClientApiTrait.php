<?php

namespace Redmine\Client;

use InvalidArgumentException;
use Redmine\Api;

/**
 * Provide API instantiation to clients
 *
 * @internal
 */
trait ClientApiTrait
{
    private array $apiInstances = [];

    private array $apiClassnames = [
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
     * @throws InvalidArgumentException if $name is not a valid api name
     */
    public function getApi(string $name): Api
    {
        if (!isset($this->apiClassnames[$name])) {
            throw new InvalidArgumentException(sprintf(
                '`%s` is not a valid api. Possible apis are `%s`',
                $name,
                implode('`, `', array_keys($this->apiClassnames))
            ));
        }
        if (isset($this->apiInstances[$name])) {
            return $this->apiInstances[$name];
        }
        $class = 'Redmine\Api\\'.$this->apiClassnames[$name];
        $this->apiInstances[$name] = new $class($this);

        return $this->apiInstances[$name];
    }
}

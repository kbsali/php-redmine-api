<?php

namespace Redmine\Client;

use Redmine\Api;
use Redmine\Exception\InvalidApiNameException;

/**
 * Provide API instantiation to clients.
 *
 * @internal
 */
trait ClientApiTrait
{
    /**
     * @var array<Api>
     */
    private array $apiInstances = [];

    /**
     * @var array<string,string>
     */
    private array $apiClassnames = [
        'attachment' => 'Redmine\Api\Attachment',
        'group' => 'Redmine\Api\Group',
        'custom_fields' => 'Redmine\Api\CustomField',
        'issue' => 'Redmine\Api\Issue',
        'issue_category' => 'Redmine\Api\IssueCategory',
        'issue_priority' => 'Redmine\Api\IssuePriority',
        'issue_relation' => 'Redmine\Api\IssueRelation',
        'issue_status' => 'Redmine\Api\IssueStatus',
        'membership' => 'Redmine\Api\Membership',
        'news' => 'Redmine\Api\News',
        'project' => 'Redmine\Api\Project',
        'query' => 'Redmine\Api\Query',
        'role' => 'Redmine\Api\Role',
        'search' => 'Redmine\Api\Search',
        'time_entry' => 'Redmine\Api\TimeEntry',
        'time_entry_activity' => 'Redmine\Api\TimeEntryActivity',
        'tracker' => 'Redmine\Api\Tracker',
        'user' => 'Redmine\Api\User',
        'version' => 'Redmine\Api\Version',
        'wiki' => 'Redmine\Api\Wiki',
    ];

    /**
     * @throws InvalidApiNameException if $name is not a valid api name
     */
    public function getApi(string $name): Api
    {
        if (!isset($this->apiClassnames[$name])) {
            throw new InvalidApiNameException(sprintf('`%s` is not a valid api. Possible apis are `%s`', $name, implode('`, `', array_keys($this->apiClassnames))));
        }
        if (isset($this->apiInstances[$name])) {
            return $this->apiInstances[$name];
        }
        $class = $this->apiClassnames[$name];
        $this->apiInstances[$name] = new $class($this);

        return $this->apiInstances[$name];
    }

    private function isUploadCall(string $path): bool
    {
        $path = strtolower($path);

        return (false !== strpos($path, '/uploads.json')) || (false !== strpos($path, '/uploads.xml'));
    }

    private function isValidFilePath(string $body): bool
    {
        return
            '' !== $body
            && strlen($body) <= \PHP_MAXPATHLEN
            && is_file(strval(str_replace("\0", '', $body)))
        ;
    }
}

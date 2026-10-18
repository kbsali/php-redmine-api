<?php

declare(strict_types=1);

namespace Redmine\Client;

use Redmine\Api;
use Redmine\Api\AbstractApi;
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

    private bool $futureMode = false;

    /**
     * @var array<string,class-string>
     */
    private array $apiClassnames = [
        'attachment' => \Redmine\Api\Attachment::class,
        'group' => \Redmine\Api\Group::class,
        'custom_fields' => \Redmine\Api\CustomField::class,
        'issue' => \Redmine\Api\Issue::class,
        'issue_category' => \Redmine\Api\IssueCategory::class,
        'issue_priority' => \Redmine\Api\IssuePriority::class,
        'issue_relation' => \Redmine\Api\IssueRelation::class,
        'issue_status' => \Redmine\Api\IssueStatus::class,
        'membership' => \Redmine\Api\Membership::class,
        'news' => \Redmine\Api\News::class,
        'project' => \Redmine\Api\Project::class,
        'query' => \Redmine\Api\Query::class,
        'role' => \Redmine\Api\Role::class,
        'search' => \Redmine\Api\Search::class,
        'time_entry' => \Redmine\Api\TimeEntry::class,
        'time_entry_activity' => \Redmine\Api\TimeEntryActivity::class,
        'tracker' => \Redmine\Api\Tracker::class,
        'user' => \Redmine\Api\User::class,
        'version' => \Redmine\Api\Version::class,
        'wiki' => \Redmine\Api\Wiki::class,
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
        $this->apiInstances[$name] = $class::fromHttpClient($this);

        if ($this->futureMode) {
            self::enableFutureModeOnApi($this->apiInstances[$name]);
        }

        return $this->apiInstances[$name];
    }

    /**
     * Throw an `UnexpectedResponseException` on unexpected response status codes.
     *
     * The mode applies to all APIs of this client, including APIs already returned by `getApi()`.
     */
    public function enableFutureMode(): void
    {
        $this->futureMode = true;

        foreach ($this->apiInstances as $api) {
            self::enableFutureModeOnApi($api);
        }
    }

    private static function enableFutureModeOnApi(Api $api): void
    {
        if (! $api instanceof AbstractApi) {
            return;
        }

        // Set the private property without adding a public method to AbstractApi
        \Closure::bind(static function (AbstractApi $api): void {
            $api->futureMode = true;
        }, null, AbstractApi::class)($api);
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

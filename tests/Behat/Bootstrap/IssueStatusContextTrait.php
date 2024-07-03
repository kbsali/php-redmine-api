<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\IssueStatus;

trait IssueStatusContextTrait
{
    /**
     * @Given I have an issue status with the name :issueStatusName
     */
    public function iHaveAnIssueStatusWithTheName($issueStatusName)
    {
        // support for creating issue status via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO issue_statuses(name, is_closed, position) VALUES(:name, :is_closed, :position);',
            [],
            [
                ':name' => $issueStatusName,
                ':is_closed' => 0,
                ':position' => 1,
            ],
        );
    }

    /**
     * @When I list all issue statuses
     */
    public function iListAllIssueStatuses()
    {
        /** @var IssueStatus */
        $api = $this->getNativeCurlClient()->getApi('issue_status');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list all issue status names
     */
    public function iListAllIssueStatusNames()
    {
        /** @var IssueStatus */
        $api = $this->getNativeCurlClient()->getApi('issue_status');

        $this->registerClientResponse(
            $api->listNames(),
            $api->getLastResponse(),
        );
    }
}

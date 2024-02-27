<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

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
}

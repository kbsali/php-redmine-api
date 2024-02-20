<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Issue;

trait IssueContextTrait
{
    /**
     * @When I create an issue with the following data
     */
    public function iCreateAnIssueWithTheFollowingData(TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->create($data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I show the issue with id :issueId
     */
    public function iShowTheIssueWithId($issueId)
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->show($issueId),
            $api->getLastResponse()
        );
    }
}

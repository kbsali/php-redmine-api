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
        $data = $this->prepareIssueData($table);

        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->create($data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I update the issue with id :issueId and the following data
     */
    public function iUpdateTheIssueWithIdAndTheFollowingData($issueId, TableNode $table)
    {
        $data = $this->prepareIssueData($table);

        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->update($issueId, $data),
            $api->getLastResponse(),
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
            $api->getLastResponse(),
        );
    }

    /**
     * @When I add the user id :userId as a watcher to the issue with id :issueId
     */
    public function iAddTheUserIdAsAWatcherToTheIssueWithId($userId, $issueId)
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->addWatcher($issueId, $userId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the user id :userId as a watcher from the issue with id :issueId
     */
    public function iRemoveTheUserIdAsAWatcherFromTheIssueWithId($userId, $issueId)
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->removeWatcher($issueId, $userId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the issue with id :issueId
     */
    public function iRemoveTheIssueWithId($issueId)
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientResponse(
            $api->remove($issueId),
            $api->getLastResponse(),
        );
    }

    private function prepareIssueData(TableNode $table): array
    {
        $data = [];

        foreach ($table as $row) {
            $key = $row['property'];
            $value = $row['value'];

            // Support for json in custom_fields
            if ($key === 'custom_fields') {
                $value = json_decode($value, true);
            }

            $data[$key] = $value;
        }

        return $data;
    }
}

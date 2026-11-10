<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Issue;

trait IssueContextTrait
{
    #[\Behat\Step\When('I create an issue with the following data')]
    public function iCreateAnIssueWithTheFollowingData(TableNode $table): void
    {
        $data = $this->prepareIssueData($table);

        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->create($data),
        );
    }

    /**
     * @param mixed $issueId
     */
    #[\Behat\Step\When('I update the issue with id :issueId and the following data')]
    public function iUpdateTheIssueWithIdAndTheFollowingData($issueId, TableNode $table): void
    {
        $data = $this->prepareIssueData($table);

        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->update($issueId, $data),
        );
    }

    /**
     * @param mixed $issueId
     */
    #[\Behat\Step\When('I show the issue with id :issueId')]
    public function iShowTheIssueWithId($issueId): void
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->show($issueId),
        );
    }

    #[\Behat\Step\When('I list all issues with the following parameters')]
    public function iListAllIssuesWithTheFollowingParameters(TableNode $table): void
    {
        $params = $this->prepareIssueData($table);

        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->list($params),
        );
    }

    /**
     * @param mixed $issueId
     */
    #[\Behat\Step\When('I show the issue with id :issueId including journals')]
    public function iShowTheIssueWithIdIncludingJournals($issueId): void
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->show($issueId, ['include' => 'journals']),
        );
    }

    /**
     * @param mixed $userId
     * @param mixed $issueId
     */
    #[\Behat\Step\When('I add the user id :userId as a watcher to the issue with id :issueId')]
    public function iAddTheUserIdAsAWatcherToTheIssueWithId($userId, $issueId): void
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->addWatcher($issueId, $userId),
        );
    }

    /**
     * @param mixed $userId
     * @param mixed $issueId
     */
    #[\Behat\Step\When('I remove the user id :userId as a watcher from the issue with id :issueId')]
    public function iRemoveTheUserIdAsAWatcherFromTheIssueWithId($userId, $issueId): void
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->removeWatcher($issueId, $userId),
        );
    }

    /**
     * @param mixed $issueId
     */
    #[\Behat\Step\When('I remove the issue with id :issueId')]
    public function iRemoveTheIssueWithId($issueId): void
    {
        /** @var Issue */
        $api = $this->getNativeCurlClient()->getApi('issue');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($issueId),
        );
    }

    /**
     * @return array<mixed>
     */
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

        return $this->replaceClearMarker($data);
    }
}

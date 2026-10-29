<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\TimeEntry;

trait TimeEntryContextTrait
{
    /**
     * @When I create a time entry with the following data
     */
    public function iCreateATimeEntryWithTheFollowingData(TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientCall(
            $api,
            fn() => $api->create($data),
        );
    }

    /**
     * @When I update the time entry with id :id and the following data
     *
     * @param mixed $id
     */
    public function iUpdateTheTimeEntryWithIdAndTheFollowingData($id, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientCall(
            $api,
            fn() => $api->update($id, $data),
        );
    }

    /**
     * @When I show the time entry with the id :activityId
     */
    public function iShowTheTimeEntryWithTheId(int $activityId): void
    {
        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientCall(
            $api,
            fn() => $api->show($activityId),
        );
    }

    /**
     * @When I remove the time entry with id :activityId
     *
     * @param mixed $activityId
     */
    public function iRemoveTheTimeEntryWithId($activityId): void
    {
        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($activityId),
        );
    }
}

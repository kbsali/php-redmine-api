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
    public function iCreateATimeEntryWithTheFollowingData(TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientResponse(
            $api->create($data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I update the time entry with id :id and the following data
     */
    public function iUpdateTheTimeEntryWithIdAndTheFollowingData($id, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientResponse(
            $api->update($id, $data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I show the time entry with the id :activityId
     */
    public function iShowTheTimeEntryWithTheId(int $activityId)
    {
        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientResponse(
            $api->show($activityId),
            $api->getLastResponse()
        );
    }

    /**
     * @When I remove the time entry with id :activityId
     */
    public function iRemoveTheTimeEntryWithId($activityId)
    {
        /** @var TimeEntry */
        $api = $this->getNativeCurlClient()->getApi('time_entry');

        $this->registerClientResponse(
            $api->remove($activityId),
            $api->getLastResponse()
        );
    }
}

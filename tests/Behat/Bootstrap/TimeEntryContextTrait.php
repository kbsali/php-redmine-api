<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
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
}
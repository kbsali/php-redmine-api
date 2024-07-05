<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\TimeEntryActivity;

trait TimeEntryActivityContextTrait
{
    /**
     * @Given I have a time entry activiy with name :activityName
     */
    public function iHaveATimeEntryActiviyWithName(string $activityName)
    {
        // support for creating time entry activity via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO enumerations(name, position, is_default, type, active) VALUES(:name, :position, :is_default, :type, :active);',
            [],
            [
                ':name' => $activityName,
                ':position' => 1,
                ':is_default' => 0,
                ':type' => 'TimeEntryActivity',
                ':active' => 1,
            ],
        );
    }

    /**
     * @When I list all time entry activities
     */
    public function iListAllTimeEntryActivities()
    {
        /** @var TimeEntryActivity */
        $api = $this->getNativeCurlClient()->getApi('time_entry_activity');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse(),
        );
    }
}

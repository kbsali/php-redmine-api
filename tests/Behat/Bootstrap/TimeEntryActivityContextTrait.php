<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

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
                ':is_default' => 1,
                ':type' => 'TimeEntryActivity',
                ':active' => 1,
            ],
        );
    }
}

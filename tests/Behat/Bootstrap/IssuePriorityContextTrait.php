<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

trait IssuePriorityContextTrait
{
    /**
     * @Given I have an issue priority with the name :priority
     */
    public function iHaveAnIssuePriorityWithTheName(string $priority)
    {
        // support for creating time entry activity via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO enumerations(name, position, is_default, type, active) VALUES(:name, :position, :is_default, :type, :active);',
            [],
            [
                ':name' => $priority,
                ':position' => 1,
                ':is_default' => 1,
                ':type' => 'IssuePriority',
                ':active' => 1,
            ],
        );
    }
}

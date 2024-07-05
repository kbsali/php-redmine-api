<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\Tracker;

trait TrackerContextTrait
{
    /**
     * @Given I have a tracker with the name :trackerName and default status id :statusId
     */
    public function iHaveATrackerWithTheNameAndDefaultStatusId(string $trackerName, int $statusId)
    {
        // support for creating tracker via REST API is missing
        $this->redmine->excecuteDatabaseQuery(
            'INSERT INTO trackers(name, position, is_in_roadmap, fields_bits, default_status_id) VALUES(:name, :position, :is_in_roadmap, :fields_bits, :default_status_id);',
            [],
            [
                ':name' => $trackerName,
                ':position' => 1,
                ':is_in_roadmap' => 1,
                ':fields_bits' => 0,
                ':default_status_id' => $statusId,
            ],
        );
    }

    /**
     * @When I list all trackers
     */
    public function iListAllTrackers()
    {
        /** @var Tracker */
        $api = $this->getNativeCurlClient()->getApi('tracker');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse(),
        );
    }
}

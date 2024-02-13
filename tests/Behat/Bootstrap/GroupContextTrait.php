<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Group;

trait GroupContextTrait
{
    /**
     * @When I create a group with name :groupName
     */
    public function iCreateAGroupWithName(string $groupName)
    {
        $table = new TableNode([
            ['property', 'value'],
            ['name', $groupName],
        ]);

        $this->iCreateAGroupWithTheFollowingData($table);
    }

    /**
     * @When I create a group with the following data
     */
    public function iCreateAGroupWithTheFollowingData(TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->create($data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I list all groups
     */
    public function iListAllGroups()
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse()
        );
    }

    /**
     * @When I show the group with id :groupId
     */
    public function iShowTheGroupWithId(int $groupId)
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->show($groupId),
            $api->getLastResponse()
        );
    }

    /**
     * @When I update the group with id :groupId with the following data
     */
    public function iUpdateTheGroupWithIdWithTheFollowingData(int $groupId, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->update($groupId, $data),
            $api->getLastResponse()
        );
    }
}

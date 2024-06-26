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
            $api->getLastResponse(),
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
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list the names of all groups
     */
    public function iListTheNamesOfAllGroups()
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->listNames(),
            $api->getLastResponse(),
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
            $api->getLastResponse(),
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
            $api->getLastResponse(),
        );
    }

    /**
     * @When I add the user with id :userId to the group with id :groupId
     */
    public function iAddTheUserWithIdToTheGroupWithId($userId, $groupId)
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->addUser($groupId, $userId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the user with id :userId from the group with id :groupId
     */
    public function iRemoveTheUserWithIdFromTheGroupWithId($userId, $groupId)
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->removeUser($groupId, $userId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the group with id :groupId
     */
    public function iRemoveTheGroupWithId($groupId)
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $api->remove($groupId),
            $api->getLastResponse(),
        );
    }
}

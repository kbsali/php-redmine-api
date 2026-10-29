<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Group;

trait GroupContextTrait
{
    /**
     * @When I create a group with name :groupName
     */
    public function iCreateAGroupWithName(string $groupName): void
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
    public function iCreateAGroupWithTheFollowingData(TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->create($data),
        );
    }

    /**
     * @When I list all groups
     */
    public function iListAllGroups(): void
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->list(),
        );
    }

    /**
     * @When I list the names of all groups
     */
    public function iListTheNamesOfAllGroups(): void
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->listNames(),
        );
    }

    /**
     * @When I show the group with id :groupId
     */
    public function iShowTheGroupWithId(int $groupId): void
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->show($groupId),
        );
    }

    /**
     * @When I update the group with id :groupId with the following data
     */
    public function iUpdateTheGroupWithIdWithTheFollowingData(int $groupId, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->update($groupId, $data),
        );
    }

    /**
     * @When I add the user with id :userId to the group with id :groupId
     *
     * @param mixed $userId
     * @param mixed $groupId
     */
    public function iAddTheUserWithIdToTheGroupWithId($userId, $groupId): void
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->addUser($groupId, $userId),
        );
    }

    /**
     * @When I remove the user with id :userId from the group with id :groupId
     *
     * @param mixed $userId
     * @param mixed $groupId
     */
    public function iRemoveTheUserWithIdFromTheGroupWithId($userId, $groupId): void
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->removeUser($groupId, $userId),
        );
    }

    /**
     * @When I remove the group with id :groupId
     *
     * @param mixed $groupId
     */
    public function iRemoveTheGroupWithId($groupId): void
    {
        /** @var Group */
        $api = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($groupId),
        );
    }
}

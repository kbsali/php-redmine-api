<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\User;

trait UserContextTrait
{
    /**
     * @Given I create :count users
     */
    public function iCreateUsers(int $count): void
    {
        while ($count > 0) {
            $table = new TableNode([
                ['property', 'value'],
                ['login', 'testuser_' . $count],
                ['firstname', 'first'],
                ['lastname', 'last'],
                ['mail', 'mail.' . $count . '@example.net'],
            ]);

            $this->iCreateAUserWithTheFollowingData($table);

            $count--;
        }
    }

    /**
     * @When I create a user with the following data
     */
    public function iCreateAUserWithTheFollowingData(TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientCall(
            $api,
            fn() => $api->create($data),
        );
    }

    /**
     * @When I show the user with id :userId
     */
    public function iShowTheUserWithId(int $userId): void
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientCall(
            $api,
            fn() => $api->show($userId),
        );
    }

    /**
     * @When I list all users
     */
    public function iListAllUsers(): void
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientCall(
            $api,
            fn() => $api->list(),
        );
    }

    /**
     * @When I list all user logins
     */
    public function iListAllUserLogins(): void
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientCall(
            $api,
            fn() => $api->listLogins(),
        );
    }

    /**
     * @When I update the user with id :id and the following data
     *
     * @param mixed $id
     */
    public function iUpdateTheUserWithIdAndTheFollowingData($id, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientCall(
            $api,
            fn() => $api->update($id, $data),
        );
    }

    /**
     * @When I remove the user with id :userId
     *
     * @param mixed $userId
     */
    public function iRemoveTheUserWithId($userId): void
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($userId),
        );
    }
}

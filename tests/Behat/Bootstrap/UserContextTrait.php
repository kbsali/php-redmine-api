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
    public function iCreateUsers(int $count)
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
    public function iCreateAUserWithTheFollowingData(TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientResponse(
            $api->create($data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I show the user with id :userId
     */
    public function iShowTheUserWithId(int $userId)
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientResponse(
            $api->show($userId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list all users
     */
    public function iListAllUsers()
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list all user logins
     */
    public function iListAllUserLogins()
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientResponse(
            $api->listLogins(),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I update the user with id :id and the following data
     */
    public function iUpdateTheUserWithIdAndTheFollowingData($id, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientResponse(
            $api->update($id, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the user with id :userId
     */
    public function iRemoveTheUserWithId($userId)
    {
        /** @var User */
        $api = $this->getNativeCurlClient()->getApi('user');

        $this->registerClientResponse(
            $api->remove($userId),
            $api->getLastResponse(),
        );
    }
}

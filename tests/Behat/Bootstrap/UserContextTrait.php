<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\User;

trait UserContextTrait
{
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
            $api->getLastResponse()
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
            $api->getLastResponse()
        );
    }
}

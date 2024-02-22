<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Redmine\Api\User;

trait UserContextTrait
{
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

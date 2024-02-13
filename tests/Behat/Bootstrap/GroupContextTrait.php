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
        $data = [
            'name' => $groupName,
        ];

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
}

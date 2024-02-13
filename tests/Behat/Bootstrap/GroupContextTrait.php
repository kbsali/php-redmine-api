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
        $groupApi = $this->getNativeCurlClient()->getApi('group');

        $this->registerClientResponse(
            $groupApi->create($data),
            $groupApi->getLastResponse()
        );
    }
}

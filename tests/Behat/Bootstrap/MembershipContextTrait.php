<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Membership;

trait MembershipContextTrait
{
    /**
     * @param mixed $identifier
     */
    #[\Behat\Step\When('I create a membership to project with identifier :identifier and the following data')]
    public function iCreateAMembershipToProjectWithIdentifierAndTheFollowingData($identifier, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        if (array_key_exists('role_ids', $data)) {
            $data['role_ids'] = json_decode($data['role_ids'], true);
        }

        /** @var Membership */
        $api = $this->getNativeCurlClient()->getApi('membership');

        $this->registerClientCall(
            $api,
            fn() => $api->create($identifier, $data),
        );
    }

    /**
     * @param mixed $id
     */
    #[\Behat\Step\When('I update the membership with id :id and the following data')]
    public function iUpdateTheMembershipWithIdAndTheFollowingData($id, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        if (array_key_exists('role_ids', $data)) {
            $data['role_ids'] = json_decode($data['role_ids'], true);
        }

        /** @var Membership */
        $api = $this->getNativeCurlClient()->getApi('membership');

        $this->registerClientCall(
            $api,
            fn() => $api->update($id, $data),
        );
    }

    /**
     * @param mixed $id
     */
    #[\Behat\Step\When('I remove the membership with id :id')]
    public function iRemoveTheMembershipWithId($id): void
    {
        /** @var Membership */
        $api = $this->getNativeCurlClient()->getApi('membership');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($id),
        );
    }

    /**
     * @param mixed $userId
     * @param mixed $identifier
     */
    #[\Behat\Step\When('I remove the user with id :userId from the project with identifier :identifier')]
    public function iRemoveTheUserWithIdFromTheProjectWithIdentifier($userId, $identifier): void
    {
        /** @var Membership */
        $api = $this->getNativeCurlClient()->getApi('membership');

        $this->registerClientCall(
            $api,
            fn() => $api->removeMember($identifier, (int) $userId),
        );
    }
}

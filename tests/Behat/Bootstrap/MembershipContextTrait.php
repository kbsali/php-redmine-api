<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Membership;

trait MembershipContextTrait
{
    /**
     * @When I create a membership to project with identifier :identifier and the following data
     */
    public function iCreateAMembershipToProjectWithIdentifierAndTheFollowingData($identifier, TableNode $table)
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

        $this->registerClientResponse(
            $api->create($identifier, $data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I update the membership with id :id and the following data
     */
    public function iUpdateTheMembershipWithIdAndTheFollowingData($id, TableNode $table)
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

        $this->registerClientResponse(
            $api->update($id, $data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I delete the membership with id :id
     */
    public function iDeleteTheMembershipWithId($id)
    {
        /** @var Membership */
        $api = $this->getNativeCurlClient()->getApi('membership');

        $this->registerClientResponse(
            $api->remove($id),
            $api->getLastResponse()
        );
    }
}

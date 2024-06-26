<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Version;

trait VersionContextTrait
{
    /**
     * @When I create a version with name :versionName and project identifier :identifier
     */
    public function iCreateAVersionWithNameAndProjectIdentifier(string $versionName, string $identifier)
    {
        $this->iCreateAVersionWithProjectIdentifierAndWithTheFollowingData(
            $identifier,
            new TableNode([
                ['property', 'value'],
                ['name', $versionName],
            ]),
        );
    }

    /**
     * @When I create a version with project identifier :identifier with the following data
     */
    public function iCreateAVersionWithProjectIdentifierAndWithTheFollowingData(string $identifier, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Version */
        $api = $this->getNativeCurlClient()->getApi('version');

        $this->registerClientResponse(
            $api->create($identifier, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I update the version with id :id and the following data
     */
    public function iUpdateTheVersionWithIdAndTheFollowingData($id, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Version */
        $api = $this->getNativeCurlClient()->getApi('version');

        $this->registerClientResponse(
            $api->update($id, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I show the version with id :versionId
     */
    public function iShowTheVersionWithId(int $versionId)
    {
        /** @var Version */
        $api = $this->getNativeCurlClient()->getApi('version');

        $this->registerClientResponse(
            $api->show($versionId),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the version with id :versionId
     */
    public function iRemoveTheVersionWithId($versionId)
    {
        /** @var Version */
        $api = $this->getNativeCurlClient()->getApi('version');

        $this->registerClientResponse(
            $api->remove($versionId),
            $api->getLastResponse(),
        );
    }
}

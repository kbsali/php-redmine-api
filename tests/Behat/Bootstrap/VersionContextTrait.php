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
            ])
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
            $api->getLastResponse()
        );
    }
}

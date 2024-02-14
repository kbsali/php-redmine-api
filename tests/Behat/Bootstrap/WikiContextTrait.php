<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Wiki;

trait WikiContextTrait
{
    /**
     * @When I create a wiki page with name :pageName and project identifier :identifier with the following data
     */
    public function iCreateAWikiPageWithNameAndProjectIdentifierWithTheFollowingData(string $pageName, string $identifier, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientResponse(
            $api->create($identifier, $pageName, $data),
            $api->getLastResponse()
        );
    }
}

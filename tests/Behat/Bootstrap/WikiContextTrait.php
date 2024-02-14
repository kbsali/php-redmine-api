<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Wiki;

trait WikiContextTrait
{
    /**
     * @When I create a wiki page with name :pageName and project identifier :identifier
     */
    public function iCreateAWikiPageWithNameAndProjectIdentifier(string $pageName, string $identifier)
    {
        $this->iCreateAWikiPageWithNameAndProjectIdentifierWithTheFollowingData(
            $pageName,
            $identifier,
            new TableNode([['property', 'value']])
        );
    }

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

    /**
     * @When I show the wiki page with name :pageName and project identifier :identifier
     */
    public function iShowTheWikiPageWithNameAndProjectIdentifier(string $pageName, string $identifier)
    {
        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientResponse(
            $api->show($identifier, $pageName),
            $api->getLastResponse()
        );
    }
}

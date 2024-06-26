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
            new TableNode([['property', 'value']]),
        );
    }

    /**
     * @When I create a wiki page with name :pageName and project identifier :identifier with the following data
     */
    public function iCreateAWikiPageWithNameAndProjectIdentifierWithTheFollowingData(string $pageName, string $identifier, TableNode $table)
    {
        $data = $this->prepareWikiData($table);

        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientResponse(
            $api->create($identifier, $pageName, $data),
            $api->getLastResponse(),
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
            $api->getLastResponse(),
        );
    }

    /**
     * @When I update the wiki page with name :pageName and project identifier :identifier with the following data
     */
    public function iUpdateTheWikiPageWithNameAndProjectIdentifierWithTheFollowingData(string $pageName, string $identifier, TableNode $table)
    {
        $data = $this->prepareWikiData($table);

        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientResponse(
            $api->update($identifier, $pageName, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the wiki page with name :pageName and project identifier :identifier
     */
    public function iRemoveTheWikiPageWithNameAndProjectIdentifier($pageName, $identifier)
    {
        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientResponse(
            $api->remove($identifier, $pageName),
            $api->getLastResponse(),
        );
    }

    private function prepareWikiData(TableNode $table): array
    {
        $data = [];

        foreach ($table as $row) {
            $key = $row['property'];
            $value = $row['value'];

            // Support for json in uploads
            if ($key === 'uploads') {
                $value = json_decode($value, true);
            }

            $data[$key] = $value;
        }

        return $data;
    }
}

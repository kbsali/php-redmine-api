<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Wiki;

trait WikiContextTrait
{
    /**
     * @When I create a wiki page with name :pageName and project identifier :identifier
     */
    public function iCreateAWikiPageWithNameAndProjectIdentifier(string $pageName, string $identifier): void
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
    public function iCreateAWikiPageWithNameAndProjectIdentifierWithTheFollowingData(string $pageName, string $identifier, TableNode $table): void
    {
        $data = $this->prepareWikiData($table);

        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientCall(
            $api,
            fn() => $api->create($identifier, $pageName, $data),
        );
    }

    /**
     * @When I show the wiki page with name :pageName and project identifier :identifier
     */
    public function iShowTheWikiPageWithNameAndProjectIdentifier(string $pageName, string $identifier): void
    {
        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientCall(
            $api,
            fn() => $api->show($identifier, $pageName),
        );
    }

    /**
     * @When I list all wiki pages for project identifier :identifier
     */
    public function iListAllWikiPagesForProjectIdentifier(string $identifier): void
    {
        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientCall(
            $api,
            fn() => $api->listByProject($identifier),
        );
    }

    /**
     * @When I update the wiki page with name :pageName and project identifier :identifier with the following data
     */
    public function iUpdateTheWikiPageWithNameAndProjectIdentifierWithTheFollowingData(string $pageName, string $identifier, TableNode $table): void
    {
        $data = $this->prepareWikiData($table);

        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientCall(
            $api,
            fn() => $api->update($identifier, $pageName, $data),
        );
    }

    /**
     * @When I remove the wiki page with name :pageName and project identifier :identifier
     *
     * @param mixed $pageName
     * @param mixed $identifier
     */
    public function iRemoveTheWikiPageWithNameAndProjectIdentifier($pageName, $identifier): void
    {
        /** @var Wiki */
        $api = $this->getNativeCurlClient()->getApi('wiki');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($identifier, $pageName),
        );
    }

    /**
     * @return array<mixed>
     */
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

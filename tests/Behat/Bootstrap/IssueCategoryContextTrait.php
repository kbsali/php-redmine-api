<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\IssueCategory;

trait IssueCategoryContextTrait
{
    /**
     * @param mixed $identifier
     */
    #[\Behat\Step\Given('I create :count issue categories for project identifier :identifier')]
    public function iCreateIssueCategoriesForProjectIdentifier(int $count, $identifier): void
    {
        while ($count > 0) {
            $this->iCreateAnIssueCategoryForProjectIdentifierAndWithTheName(
                $identifier,
                'Issue Category ' . $count,
            );

            $count--;
        }
    }

    /**
     * @param mixed $identifier
     * @param mixed $name
     */
    #[\Behat\Step\When('I create an issue category for project identifier :identifier and with the name :name')]
    public function iCreateAnIssueCategoryForProjectIdentifierAndWithTheName($identifier, $name): void
    {
        $table = new TableNode([
            ['property', 'value'],
            ['name', $name],
        ]);

        $this->iCreateAnIssueCategoryForProjectIdentifierAndWithTheFollowingData($identifier, $table);
    }

    /**
     * @param mixed $identifier
     */
    #[\Behat\Step\When('I create an issue category for project identifier :identifier and with the following data')]
    public function iCreateAnIssueCategoryForProjectIdentifierAndWithTheFollowingData($identifier, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientCall(
            $api,
            fn() => $api->create($identifier, $data),
        );
    }

    /**
     * @param mixed $identifier
     */
    #[\Behat\Step\When('I list all issue categories for project identifier :identifier')]
    public function iListAllIssueCategoriesForProjectIdentifier($identifier): void
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientCall(
            $api,
            fn() => $api->listByProject($identifier),
        );
    }

    /**
     * @param mixed $identifier
     */
    #[\Behat\Step\When('I list all issue category names for project identifier :identifier')]
    public function iListAllIssueCategoryNamesForProjectIdentifier($identifier): void
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientCall(
            $api,
            fn() => $api->listNamesByProject($identifier),
        );
    }

    /**
     * @param mixed $id
     */
    #[\Behat\Step\When('I show the issue category with id :id')]
    public function iShowTheIssueCategoryWithId($id): void
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientCall(
            $api,
            fn() => $api->show($id),
        );
    }

    /**
     * @param mixed $id
     */
    #[\Behat\Step\When('I update the issue category with id :id and the following data')]
    public function iUpdateTheIssueCategoryWithIdAndTheFollowingData($id, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientCall(
            $api,
            fn() => $api->update($id, $data),
        );
    }

    /**
     * @param mixed $id
     */
    #[\Behat\Step\When('I remove the issue category with id :id')]
    public function iRemoveTheIssueCategoryWithId($id): void
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($id),
        );
    }
}

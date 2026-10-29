<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\IssueCategory;

trait IssueCategoryContextTrait
{
    /**
     * @Given I create :count issue categories for project identifier :identifier
     *
     * @param mixed $identifier
     */
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
     * @When I create an issue category for project identifier :identifier and with the name :name
     *
     * @param mixed $identifier
     * @param mixed $name
     */
    public function iCreateAnIssueCategoryForProjectIdentifierAndWithTheName($identifier, $name): void
    {
        $table = new TableNode([
            ['property', 'value'],
            ['name', $name],
        ]);

        $this->iCreateAnIssueCategoryForProjectIdentifierAndWithTheFollowingData($identifier, $table);
    }

    /**
     * @When I create an issue category for project identifier :identifier and with the following data
     *
     * @param mixed $identifier
     */
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
     * @When I list all issue categories for project identifier :identifier
     *
     * @param mixed $identifier
     */
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
     * @When I list all issue category names for project identifier :identifier
     *
     * @param mixed $identifier
     */
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
     * @When I show the issue category with id :id
     *
     * @param mixed $id
     */
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
     * @When I update the issue category with id :id and the following data
     *
     * @param mixed $id
     */
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
     * @When I remove the issue category with id :id
     *
     * @param mixed $id
     */
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

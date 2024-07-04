<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\IssueCategory;

trait IssueCategoryContextTrait
{
    /**
     * @Given I create :count issue categories for project identifier :identifier
     */
    public function iCreateIssueCategoriesForProjectIdentifier(int $count, $identifier)
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
     */
    public function iCreateAnIssueCategoryForProjectIdentifierAndWithTheName($identifier, $name)
    {
        $table = new TableNode([
            ['property', 'value'],
            ['name', $name],
        ]);

        $this->iCreateAnIssueCategoryForProjectIdentifierAndWithTheFollowingData($identifier, $table);
    }

    /**
     * @When I create an issue category for project identifier :identifier and with the following data
     */
    public function iCreateAnIssueCategoryForProjectIdentifierAndWithTheFollowingData($identifier, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientResponse(
            $api->create($identifier, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list all issue categories for project identifier :identifier
     */
    public function iListAllIssueCategoriesForProjectIdentifier($identifier)
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientResponse(
            $api->listByProject($identifier),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I list all issue category names for project identifier :identifier
     */
    public function iListAllIssueCategoryNamesForProjectIdentifier($identifier)
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientResponse(
            $api->listNamesByProject($identifier),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I update the issue category with id :id and the following data
     */
    public function iUpdateTheIssueCategoryWithIdAndTheFollowingData($id, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientResponse(
            $api->update($id, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I remove the issue category with id :id
     */
    public function iRemoveTheIssueCategoryWithId($id)
    {
        /** @var IssueCategory */
        $api = $this->getNativeCurlClient()->getApi('issue_category');

        $this->registerClientResponse(
            $api->remove($id),
            $api->getLastResponse(),
        );
    }
}

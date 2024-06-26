<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Gherkin\Node\TableNode;
use Redmine\Api\IssueRelation;

trait IssueRelationContextTrait
{
    /**
     * @When I create an issue relation for issue id :issueId with the following data
     */
    public function iCreateAnIssueRelationForIssueIdWithTheFollowingData(int $issueId, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var IssueRelation */
        $api = $this->getNativeCurlClient()->getApi('issue_relation');

        $this->registerClientResponse(
            $api->create($issueId, $data),
            $api->getLastResponse(),
        );
    }

    /**
     * @When I delete the issue relation with the id :relationId
     */
    public function iDeleteTheIssueRelationWithTheId($relationId)
    {
        /** @var IssueRelation */
        $api = $this->getNativeCurlClient()->getApi('issue_relation');

        $this->registerClientResponse(
            $api->remove($relationId),
            $api->getLastResponse(),
        );
    }
}

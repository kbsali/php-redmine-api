<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Redmine\Api\Project;

trait ProjectContextTrait
{
    /**
     * @When I create a project with name :name and identifier :identifier
     */
    public function iCreateAProjectWithNameAndIdentifier(string $name, string $identifier)
    {
        $table = new TableNode([
            ['property', 'value'],
            ['name', $name],
            ['identifier', $identifier],
        ]);

        $this->iCreateAProjectWithTheFollowingData($table);
    }

    /**
     * @When I create a project with the following data
     */
    public function iCreateAProjectWithTheFollowingData(TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Project */
        $projectApi = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $projectApi->create($data),
            $projectApi->getLastResponse()
        );
    }

    /**
     * @When I list all projects
     */
    public function iListAllProjects()
    {
        /** @var Project */
        $projectApi = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $projectApi->list(),
            $projectApi->getLastResponse()
        );
    }

    /**
     * @When I update the project with identifier :identifier with the following data
     */
    public function iUpdateTheProjectWithIdentifierWithTheFollowingData(string $identifier, TableNode $table)
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Project */
        $projectApi = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $projectApi->update($identifier, $data),
            $projectApi->getLastResponse()
        );
    }
}

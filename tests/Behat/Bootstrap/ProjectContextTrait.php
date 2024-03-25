<?php

declare(strict_types=1);

namespace Redmine\Tests\Behat\Bootstrap;

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
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->create($data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I list all projects
     */
    public function iListAllProjects()
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->list(),
            $api->getLastResponse()
        );
    }

    /**
     * @When I show the project with identifier :identifier
     */
    public function iShowTheProjectWithIdentifier(string $identifier)
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->show($identifier),
            $api->getLastResponse()
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
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->update($identifier, $data),
            $api->getLastResponse()
        );
    }

    /**
     * @When I close the project with identifier :identifier
     */
    public function iCloseTheProjectWithIdentifier(string $identifier)
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->close($identifier),
            $api->getLastResponse()
        );
    }

    /**
     * @When I reopen the project with identifier :identifier
     */
    public function iReopenTheProjectWithIdentifier(string $identifier)
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->reopen($identifier),
            $api->getLastResponse()
        );
    }

    /**
     * @When I archive the project with identifier :identifier
     */
    public function iArchiveTheProjectWithIdentifier(string $identifier)
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->archive($identifier),
            $api->getLastResponse()
        );
    }

    /**
     * @When I unarchive the project with identifier :identifier
     */
    public function iUnarchiveTheProjectWithIdentifier(string $identifier)
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->unarchive($identifier),
            $api->getLastResponse()
        );
    }

    /**
     * @When I remove the project with identifier :identifier
     */
    public function iRemoveTheProjectWithIdentifier($identifier)
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientResponse(
            $api->remove($identifier),
            $api->getLastResponse()
        );
    }
}

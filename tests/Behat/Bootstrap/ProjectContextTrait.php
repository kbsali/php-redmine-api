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
    public function iCreateAProjectWithNameAndIdentifier(string $name, string $identifier): void
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
    public function iCreateAProjectWithTheFollowingData(TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->create($data),
        );
    }

    /**
     * @Given I create :count projects
     */
    public function iCreateProjects(int $count): void
    {
        while ($count > 0) {
            $this->iCreateAProjectWithNameAndIdentifier('Test Project ' . $count, 'test-project-' . $count);

            $count--;
        }
    }

    /**
     * @When I list all projects
     */
    public function iListAllProjects(): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->list(),
        );
    }

    /**
     * @When I list all project names
     */
    public function iListAllProjectNames(): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->listNames(),
        );
    }

    /**
     * @When I show the project with identifier :identifier
     */
    public function iShowTheProjectWithIdentifier(string $identifier): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->show($identifier),
        );
    }

    /**
     * @When I update the project with identifier :identifier with the following data
     */
    public function iUpdateTheProjectWithIdentifierWithTheFollowingData(string $identifier, TableNode $table): void
    {
        $data = [];

        foreach ($table as $row) {
            $data[$row['property']] = $row['value'];
        }

        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->update($identifier, $data),
        );
    }

    /**
     * @When I close the project with identifier :identifier
     */
    public function iCloseTheProjectWithIdentifier(string $identifier): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->close($identifier),
        );
    }

    /**
     * @When I reopen the project with identifier :identifier
     */
    public function iReopenTheProjectWithIdentifier(string $identifier): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->reopen($identifier),
        );
    }

    /**
     * @When I archive the project with identifier :identifier
     */
    public function iArchiveTheProjectWithIdentifier(string $identifier): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->archive($identifier),
        );
    }

    /**
     * @When I unarchive the project with identifier :identifier
     */
    public function iUnarchiveTheProjectWithIdentifier(string $identifier): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->unarchive($identifier),
        );
    }

    /**
     * @When I remove the project with identifier :identifier
     *
     * @param mixed $identifier
     */
    public function iRemoveTheProjectWithIdentifier($identifier): void
    {
        /** @var Project */
        $api = $this->getNativeCurlClient()->getApi('project');

        $this->registerClientCall(
            $api,
            fn() => $api->remove($identifier),
        );
    }
}

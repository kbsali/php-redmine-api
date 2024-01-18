<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Project;

use DateTimeImmutable;
use Redmine\Api\Project;
use Redmine\Tests\End2End\ClientTestCase;
use Redmine\Tests\RedmineExtension\RedmineVersion;

class ClosingProjectTest extends ClientTestCase
{
    /**
     * @dataProvider provideRedmineVersions
     */
    public function testInteractionWithProject(RedmineVersion $redmineVersion): void
    {
        $client = $this->getNativeCurlClient($redmineVersion);

        /** @var Project */
        $api = $client->getApi('project');
        $now = new DateTimeImmutable();

        // Create project
        $projectName = 'test project';
        $projectIdentifier = 'test_project';

        $xmlData = $api->create([
            'name' => $projectName,
            'identifier' => $projectIdentifier,
        ]);

        $projectDataJson = json_encode($xmlData);
        $projectData = json_decode($projectDataJson, true);

        $this->assertIsArray($projectData, $projectDataJson);
        $this->assertArrayHasKey('identifier', $projectData, $projectDataJson);
        $this->assertSame($projectIdentifier, $projectData['identifier'], $projectDataJson);
        $this->assertArrayHasKey('status', $projectData, $projectDataJson);
        $this->assertSame('1', $projectData['status'], $projectDataJson);

        // Close project
        $result = $api->close($projectIdentifier);
        $this->assertSame('', $result);

        // Read single project
        $projectDetails = $api->show($projectIdentifier);

        $this->assertArrayHasKey('project', $projectDetails);
        $this->assertSame(
            [
                'id',
                'name',
                'identifier',
            ],
            $projectDetails['project']
        );
        $this->assertArrayHasKey('status', $projectDetails['project']);
        $this->assertSame('5', $projectDetails['project']['status']);
    }
}

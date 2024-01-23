<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Project;

use Redmine\Api\Project;
use Redmine\Tests\End2End\ClientTestCase;
use Redmine\Tests\RedmineExtension\RedmineVersion;

class ArchivingProjectTest extends ClientTestCase
{
    /**
     * @dataProvider provideRedmineVersions
     */
    public function testInteractionWithProject(RedmineVersion $redmineVersion): void
    {
        $client = $this->getNativeCurlClient($redmineVersion);

        /** @var Project */
        $api = $client->getApi('project');

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

        // Archive project
        $this->assertTrue($api->archive($projectIdentifier));

        // Reading an archived project is not possible
        $this->assertFalse($api->show($projectIdentifier));

        // Unarchive project
        $this->assertTrue($api->unarchive($projectIdentifier));

        // Read single project
        $projectDetails = $api->show($projectIdentifier);

        $this->assertArrayHasKey('project', $projectDetails);
        $this->assertSame(
            [
                'id',
                'name',
                'identifier',
                'description',
                'homepage',
                'status',
                'is_public',
                'inherit_members',
                'trackers',
                'issue_categories',
                'created_on',
                'updated_on',
            ],
            array_keys($projectDetails['project'])
        );
        $this->assertSame(1, $projectDetails['project']['status']);
    }
}

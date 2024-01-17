<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Project;

use DateTimeImmutable;
use Redmine\Api\Project;
use Redmine\Tests\End2End\ClientTestCase;
use Redmine\Tests\RedmineExtension\RedmineVersion;

class ProjectTest extends ClientTestCase
{
    public static function getRedmineVersions(): array
    {
        $data = [];

        foreach (static::getAvailableRedmineVersions() as $version) {
            $data[] = [$version];
        }

        return $data;
    }

    /**
     * @dataProvider getRedmineVersions
     */
    public function testInteractionWithProject(RedmineVersion $redmineVersion): void
    {
        $client = $this->getNativeCurlClient($redmineVersion);

        /** @var Project */
        $api = $client->getApi('project');
        $now = new DateTimeImmutable();

        // Create project
        $projectName = 'test project ' . $now->format('Y-m-d H:i:s');
        $projectIdentifier = str_replace([' ', ':'], ['_', '-'], $projectName);

        $xmlData = $api->create([
            'name' => $projectName,
            'identifier' => $projectIdentifier,
        ]);

        $projectData = json_decode(json_encode($xmlData), true);

        $this->assertIsArray($projectData, json_encode($projectData));
        $this->assertIsString($projectData['id'], json_encode($projectData));
        $this->assertSame($projectName, $projectData['name'], json_encode($projectData));
        $this->assertSame($projectIdentifier, $projectData['identifier'], json_encode($projectData));
    }
}

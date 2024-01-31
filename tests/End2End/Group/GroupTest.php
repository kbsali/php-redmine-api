<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Group;

use DateTimeImmutable;
use Redmine\Api\Group;
use Redmine\Tests\End2End\ClientTestCase;
use Redmine\Tests\RedmineExtension\RedmineVersion;

class GroupTest extends ClientTestCase
{
    /**
     * @dataProvider provideRedmineVersions
     */
    public function testInteractionWithGroup(RedmineVersion $redmineVersion): void
    {
        $client = $this->getNativeCurlClient($redmineVersion);

        /** @var Group */
        $groupApi = $client->getApi('group');
        $now = new DateTimeImmutable();

        // Create group
        $groupName = 'test group ' . $now->format('Y-m-d H:i:s');

        $xmlData = $groupApi->create([
            'name' => $groupName,
        ]);

        $jsonData = json_encode($xmlData);

        $groupData = json_decode($jsonData, true);

        $this->assertIsArray($groupData, $jsonData);
        $this->assertIsString($groupData['id'], $jsonData);
        $this->assertSame($groupName, $groupData['name'], $jsonData);

        $groupId = (int) $groupData['id'];

        // List groups
        $this->assertSame(
            [
                'groups' => [
                    [
                        'id' => $groupId,
                        'name' => $groupName,
                    ],
                ],
            ],
            $groupApi->list()
        );

        // Read group
        $this->assertSame(
            [
                'group' => [
                    'id' => $groupId,
                    'name' => $groupName,
                ]
            ],
            $groupApi->show($groupId)
        );

        // Update group
        $result = $groupApi->update($groupId, ['name' => 'new group name']);
        $this->assertSame('', $result);

        $this->assertSame(
            [
                'group' => [
                    'id' => $groupId,
                    'name' => 'new group name',
                ]
            ],
            $groupApi->show($groupId)
        );
    }
}

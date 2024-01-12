<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Group;

use DateTimeImmutable;
use Redmine\Api\Group;
use Redmine\Tests\End2End\ClientTestCase;

class GroupTest extends ClientTestCase
{
    public static function getRedmineVersions(): array
    {
        $data = [];

        foreach (static::getAvailableRedmineVersions() as $redmineVersion) {
            $data[] = [$redmineVersion];
        }

        return $data;
    }

    /**
     * @dataProvider getRedmineVersions
     */
    public function testInteractionWithGroup(string $redmineVersion): void
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

        $groupData = json_decode(json_encode($xmlData), true);

        $this->assertIsArray($groupData, json_encode($groupData));
        $this->assertIsString($groupData['id']);
        $this->assertSame($groupName, $groupData['name']);

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

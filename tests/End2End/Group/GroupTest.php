<?php

declare(strict_types=1);

namespace Redmine\Tests\End2End\Group;

use DateTimeImmutable;
use Redmine\Api\Group;
use Redmine\Tests\End2End\ClientTestCase;

class GroupTest extends ClientTestCase
{
    public function testInteractionWithGroup(): void
    {
        $client = $this->getNativeCurlClient();

        /** @var Group */
        $groupApi = $client->getApi('group');
        $now = new DateTimeImmutable();

        // Create group
        $groupName = 'test group ' . $now->format('Y-m-d H:i:s');

        $xmlData = $groupApi->create([
            'name' => $groupName,
        ]);

        $data = json_decode(json_encode($xmlData), true);

        $this->assertIsArray($data, json_encode($data));
        $this->assertIsString($data['id']);
        $this->assertSame($groupName, $data['name']);

        $groupId = (int) $data['id'];

        // List groups
        $data = $groupApi->list();

        $this->assertSame(
            [
                'groups' => [
                    [
                        'id' => $groupId,
                        'name' => $groupName,
                    ],
                ],
            ],
            $data
        );

        // Read group
        $data = $groupApi->show($groupId);

        $this->assertSame(
            [
                'group' => [
                    'id' => $groupId,
                    'name' => $groupName,
                ]
            ],
            $data
        );
    }
}

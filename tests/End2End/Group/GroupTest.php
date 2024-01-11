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

        // Create group
        $xmlData = $groupApi->create([
            'name' => 'test group ' . (new DateTimeImmutable())->format('Y-m-d H:i:s'),
        ]);

        $data = json_decode(json_encode($xmlData), true);

        $this->assertIsArray($data, json_encode($data));
        $this->assertIsString($data['id']);
        $this->assertStringStartsWith('test group ', $data['name']);
    }
}

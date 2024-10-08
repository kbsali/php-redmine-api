<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Group::class)]
class RemoveTest extends TestCase
{
    public function testRemoveReturnsString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/groups/5.xml',
                'application/xml',
                '',
                204,
                '',
                '',
            ],
        );

        $api = new Group($client);

        $this->assertSame('', $api->remove(5));
    }
}

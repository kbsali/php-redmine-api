<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Group::class)]
class RemoveUserTest extends TestCase
{
    public function testRemoveUserReturnsString()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/groups/5/users/10.xml',
                'application/xml',
                '',
                204,
                '',
                '',
            ]
        );

        $api = new Group($client);

        $this->assertSame('', $api->removeUser(5, 10));
    }
}

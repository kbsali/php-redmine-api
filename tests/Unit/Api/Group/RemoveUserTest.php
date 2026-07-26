<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Group::class)]
class RemoveUserTest extends TestCase
{
    public function testRemoveUserReturnsString(): void
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
            ],
        );

        $api = Group::fromHttpClient($client);

        $this->assertSame('', $api->removeUser(5, 10));
    }

    public function testRemoveUserWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/groups/5/users/10.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Group::fromHttpClient($client);

        $this->assertSame('', $api->removeUser(5, 10));
    }

    public function testRemoveUserWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/groups/5/users/10.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Group::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->removeUser(5, 10);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

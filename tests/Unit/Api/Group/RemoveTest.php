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

        $api = Group::fromHttpClient($client);

        $this->assertSame('', $api->remove(5));
    }

    public function testRemoveWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/groups/5.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Group::fromHttpClient($client);

        $this->assertSame('', $api->remove(5));
    }

    public function testRemoveWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/groups/5.xml',
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

            $api->remove(5);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

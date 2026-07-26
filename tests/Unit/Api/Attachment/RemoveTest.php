<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Attachment::class)]
class RemoveTest extends TestCase
{
    public function testRemoveReturnsString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/attachments/5.xml',
                'application/xml',
                '',
                204,
                '',
                '',
            ],
        );

        $api = Attachment::fromHttpClient($client);

        $this->assertSame('', $api->remove(5));
    }

    public function testRemoveWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/attachments/5.xml',
                'application/xml',
                '',
                500,
                '',
                'error body',
            ],
        );

        $api = Attachment::fromHttpClient($client);

        $this->assertSame('error body', $api->remove(5));
    }

    public function testRemoveWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/attachments/5.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Attachment::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->remove(5);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

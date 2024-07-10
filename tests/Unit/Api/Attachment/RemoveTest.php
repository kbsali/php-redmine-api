<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
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

        $api = new Attachment($client);

        $this->assertSame('', $api->remove(5));
    }
}

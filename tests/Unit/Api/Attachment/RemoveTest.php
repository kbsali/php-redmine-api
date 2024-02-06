<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Attachment::remove
 */
class RemoveTest extends TestCase
{
    public function testRemoveReturnsString()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/attachments/5.xml',
                'application/xml',
                '',
                204,
                'application/xml',
                ''
            ]
        );

        $api = new Attachment($client);

        $this->assertSame('', $api->remove(5));
    }
}

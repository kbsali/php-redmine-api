<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\Attachment
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class AttachmentTest extends TestCase
{
    /**
     * Test lastCallFailed().
     *
     * @covers       ::__construct
     * @covers       ::lastCallFailed
     * @dataProvider responseCodeProvider
     *
     * @param int  $responseCode
     * @param bool $hasFailed
     */
    #[DataProvider('responseCodeProvider')]
    public function testLastCallFailedTrue($responseCode, $hasFailed)
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('getLastResponseStatusCode')
            ->willReturn($responseCode);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($hasFailed, $api->lastCallFailed());
    }

    /**
     * Data provider for response code and expected state.
     *
     * @return array[]
     */
    public static function responseCodeProvider(): array
    {
        return [
            [199, true],
            [200, false],
            [201, false],
            [202, true],
            [400, true],
            [403, true],
            [404, true],
            [500, true],
        ];
    }
}

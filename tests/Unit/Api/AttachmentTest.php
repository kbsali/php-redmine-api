<?php

namespace Redmine\Tests\Unit\Api;

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
     * @test
     */
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
    public function responseCodeProvider()
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

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowReturnsClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->equalTo('/attachments/5.json'))
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($response, $api->show(5));
    }

    /**
     * Test download().
     *
     * @covers ::get
     * @covers ::download
     * @test
     */
    public function testDownloadReturnsUndecodedClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->equalTo('/attachments/download/5'))
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($response, $api->download(5));
    }

    /**
     * Test upload().
     *
     * @covers ::post
     * @covers ::upload
     * @test
     */
    public function testUploadReturnsClientPostResponse()
    {
        // Test values
        $postRequestData = 'API Response';
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                $this->stringStartsWith('/uploads.json'),
                $this->equalTo($postRequestData)
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($response, $api->upload($postRequestData));
    }
}

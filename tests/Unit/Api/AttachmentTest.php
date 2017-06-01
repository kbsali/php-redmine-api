<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Attachment;

/**
 * @coversDefaultClass \Redmine\Api\Attachment
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class AttachmentTest extends \PHPUnit\Framework\TestCase
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
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('getResponseCode')
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/attachments/5.json'))
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/attachments/download/5'), false)
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->download(5));
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
        $postResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->equalTo('/uploads.json'),
                $this->equalTo($postRequestData)
            )
            ->willReturn($postResponse);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($postResponse, $api->upload($postRequestData));
    }
}

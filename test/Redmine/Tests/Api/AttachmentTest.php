<?php

namespace Redmine\Tests\Api;

use Redmine\Api\Attachment;

/**
 * @coversDefaultClass Redmine\Api\Attachment
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class AttachmentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test lastCallFailed().
     *
     * @covers       ::__construct
     * @covers       ::lastCallFailed
     * @dataProvider responseCodeProvider
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
        return array(
            array(199, true),
            array(200, false),
            array(201, false),
            array(202, true),
            array(400, true),
            array(403, true),
            array(404, true),
            array(500, true),
        );
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
            ->with($this->equalTo('/attachments/5'), false)
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

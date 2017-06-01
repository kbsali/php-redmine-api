<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\News;

/**
 * @coversDefaultClass \Redmine\Api\News
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class NewsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponse()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/news.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new News($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithProject()
    {
        // Test values
        $projectId = 5;
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/news.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new News($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($projectId));
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParametersAndProject()
    {
        // Test values
        $projectId = 5;
        $parameters = ['not-used'];
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->stringContains('not-used')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new News($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($projectId, $parameters));
    }
}

<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssuePriority;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\IssuePriority
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssuePriorityTest extends TestCase
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/issue_priorities.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssuePriority($client);

        // Perform the tests
        $this->assertSame($response, $api->all());
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParameters()
    {
        // Test values
        $allParameters = ['not-used'];
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringContains('not-used')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssuePriority($client);

        // Perform the tests
        $this->assertSame([$response], $api->all($allParameters));
    }
}

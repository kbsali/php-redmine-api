<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Query;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\Query
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class QueryTest extends TestCase
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
                $this->stringStartsWith('/queries.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Query($client);

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
        $parameters = ['not-used'];
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/queries.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Query($client);

        // Perform the tests
        $this->assertSame([$response], $api->all($parameters));
    }
}

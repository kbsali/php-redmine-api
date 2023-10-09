<?php

namespace Redmine\Tests\Unit\Api\News;

use PHPUnit\Framework\TestCase;
use Redmine\Api\News;
use Redmine\Client\Client;

/**
 * Tests for News::list()
 */
class ListTest extends TestCase
{
    /**
     * @covers \Redmine\Api\News::list
     */
    public function testListWithoutParametersReturnsResponse()
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/news.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new News($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list());
    }

    /**
     * @covers \Redmine\Api\News::list
     */
    public function testListWithParametersReturnsResponse()
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/news.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new News($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($parameters));
    }
}

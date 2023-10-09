<?php

namespace Redmine\Tests\Unit\Api\IssuePriority;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssuePriority;
use Redmine\Client\Client;

/**
 * Tests for IssuePriority::list()
 */
class ListTest extends TestCase
{
    /**
     * @covers \Redmine\Api\IssuePriority::list
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
                $this->stringStartsWith('/enumerations/issue_priorities.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssuePriority($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list());
    }

    /**
     * @covers \Redmine\Api\IssuePriority::list
     */
    public function testListWithParametersReturnsResponse()
    {
        // Test values
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringContains('not-used')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssuePriority($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($allParameters));
    }
}

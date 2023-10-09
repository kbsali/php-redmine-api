<?php

namespace Redmine\Tests\Unit\Api\IssueStatus;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueStatus;
use Redmine\Client\Client;

/**
 * Tests for IssueStatus::list()
 */
class ListTest extends TestCase
{
    /**
     * @covers \Redmine\Api\IssueStatus::list
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
                $this->stringStartsWith('/issue_statuses.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueStatus($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list());
    }

    /**
     * @covers \Redmine\Api\IssueStatus::list
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
                    $this->stringStartsWith('/issue_statuses.json'),
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
        $api = new IssueStatus($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($parameters));
    }
}
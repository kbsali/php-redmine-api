<?php

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Client\Client;

/**
 * @covers \Redmine\Api\IssueRelation::listByIssueId
 */
class ListByIssueIdTest extends TestCase
{
    public function testListByIssueIdWithoutParametersReturnsResponse()
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/issues/5/relations.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByIssueId(5));
    }

    public function testListByIssueIdWithParametersReturnsResponse()
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/issues/5/relations.json?limit=25&offset=0&0=not-used')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listByIssueId(5, $parameters));
    }
}

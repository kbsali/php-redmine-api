<?php

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Client\Client;
use Redmine\Exception\UnexpectedResponseException;

#[CoversClass(IssueRelation::class)]
class ListByIssueIdTest extends TestCase
{
    public function testListByIssueIdWithoutParametersReturnsResponse(): void
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

    public function testListByIssueIdWithParametersReturnsResponse(): void
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

    public function testListByIssueIdThrowsException(): void
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/issues/5/relations.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn('');
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueRelation($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByIssueId(5);
    }
}

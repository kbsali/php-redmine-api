<?php

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Client\Client;
use Redmine\Exception\InvalidParameterException;
use Redmine\Tests\Fixtures\MockClient;
use stdClass;

/**
 * Tests for IssueCategory::list()
 */
class ListTest extends TestCase
{
    /**
     * @covers \Redmine\Api\IssueCategory::list
     */
    public function testListWithoutParametersReturnsResponse()
    {
        // Test values
        $projectId = 5;
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($projectId));
    }

    /**
     * @covers \Redmine\Api\IssueCategory::list
     */
    public function testListWithParametersReturnsResponse()
    {
        // Test values
        $projectId = 'project-slug';
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/project-slug/issue_categories.json'),
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
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($projectId, $parameters));
    }

    /**
     * @covers \Redmine\Api\IssueCategory::list
     *
     * @dataProvider getInvalidProjectIdentifiers
     */
    public function testListWithWrongProjectIdentifierThrowsException($projectIdentifier)
    {
        $api = new IssueCategory(MockClient::create());

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\IssueCategory::list(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->list($projectIdentifier);
    }

    public static function getInvalidProjectIdentifiers(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'float' => [0.0],
            'array' => [[]],
            'object' => [new stdClass()],
        ];
    }
}

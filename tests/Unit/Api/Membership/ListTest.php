<?php

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Client\Client;
use Redmine\Exception\InvalidParameterException;
use Redmine\Tests\Fixtures\MockClient;
use stdClass;

/**
 * Tests for Membership::list()
 */
class ListTest extends TestCase
{
    /**
     * @covers \Redmine\Api\Membership::list
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
                $this->stringStartsWith('/projects/5/memberships.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list(5));
    }

    /**
     * @covers \Redmine\Api\Membership::list
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
                    $this->stringStartsWith('/projects/project-slug/memberships.json'),
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
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list('project-slug', $parameters));
    }

    /**
     * @covers \Redmine\Api\Membership::list
     *
     * @dataProvider getInvalidProjectIdentifiers
     */
    public function testListWithWrongProjectIdentifierThrowsException($projectIdentifier)
    {
        $api = new Membership(MockClient::create());

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\Membership::list(): Argument #1 ($projectIdentifier) must be of type int or string');

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
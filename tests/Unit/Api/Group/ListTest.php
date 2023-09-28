<?php

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;

/**
 * Tests for Group::list()
 */
class ListTest extends TestCase
{
    /**
     * @covers \Redmine\Api\Group::list
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
                $this->stringStartsWith('/groups.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list());
    }

    /**
     * @covers \Redmine\Api\Group::all
     */
    public function testListeWithParametersReturnsResponse()
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
                    $this->stringStartsWith('/groups.json'),
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
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($parameters));
    }
}

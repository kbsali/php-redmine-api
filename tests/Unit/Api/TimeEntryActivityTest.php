<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntryActivity;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\TimeEntryActivity
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class TimeEntryActivityTest extends TestCase
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new TimeEntryActivity($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all());
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/enumerations/time_entry_activities.json'),
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
        $api = new TimeEntryActivity($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    public function testListingReturnsNameIdArray()
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivities 1"},{"id":2,"name":"TimeEntryActivities 2"}]}';
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = $this->createMock(Client::class);
        $client->expects($this->atLeastOnce())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new TimeEntryActivity($client);

        $this->assertSame($expectedReturn, $api->listing());
    }

    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivities 1"},{"id":2,"name":"TimeEntryActivities 2"}]}';
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new TimeEntryActivity($client);

        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    public function testGetIdByNameMakesGetRequest()
    {
        $response = '{"time_entry_activities":[{"id":2,"name":"TimeEntryActivities 2"}]}';

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new TimeEntryActivity($client);

        $this->assertFalse($api->getIdByName('TimeEntryActivities 1'));
        $this->assertSame(2, $api->getIdByName('TimeEntryActivities 2'));
    }
}

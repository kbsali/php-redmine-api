<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntryActivity;

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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new TimeEntryActivity($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
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
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/enumerations/time_entry_activities.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new TimeEntryActivity($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($parameters));
    }

    public function testListingReturnsNameIdArray()
    {
        $response = [
            'time_entry_activities' => [
                ['id' => 1, 'name' => 'TimeEntryActivities 1'],
                ['id' => 2, 'name' => 'TimeEntryActivities 2'],
            ],
        ];
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn($response);
        $api = new TimeEntryActivity($client);
        $this->assertSame($expectedReturn, $api->listing());
    }

    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        $response = [
            'time_entry_activities' => [
                ['id' => 1, 'name' => 'TimeEntryActivities 1'],
                ['id' => 2, 'name' => 'TimeEntryActivities 2'],
            ],
        ];
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn($response);
        $api = new TimeEntryActivity($client);

        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    public function testGetIdByNameMakesGetRequest()
    {
        $response = [
            'time_entry_activities' => [
                ['id' => 2, 'name' => 'TimeEntryActivities 2'],
            ],
        ];

        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/enumerations/time_entry_activities.json')
            )
            ->willReturn($response);
        $api = new TimeEntryActivity($client);

        $this->assertFalse($api->getIdByName('TimeEntryActivities 1'));
        $this->assertSame(2, $api->getIdByName('TimeEntryActivities 2'));
    }
}

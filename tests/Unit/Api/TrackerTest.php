<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Tracker;

/**
 * @coversDefaultClass \Redmine\Api\Tracker
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class TrackerTest extends \PHPUnit\Framework\TestCase
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
                $this->stringStartsWith('/trackers.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParametersAndProject()
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
                    $this->stringStartsWith('/trackers.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($parameters));
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingReturnsNameIdArray()
    {
        // Test values
        $getResponse = [
            'trackers' => [
                ['id' => 1, 'name' => 'Tracker 1'],
                ['id' => 5, 'name' => 'Tracker 5'],
            ],
        ];
        $expectedReturn = [
            'Tracker 1' => 1,
            'Tracker 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/trackers.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetOnlyTheFirstTime()
    {
        // Test values
        $getResponse = [
            'trackers' => [
                ['id' => 1, 'name' => 'Tracker 1'],
                ['id' => 5, 'name' => 'Tracker 5'],
            ],
        ];
        $expectedReturn = [
            'Tracker 1' => 1,
            'Tracker 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/trackers.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        // Test values
        $getResponse = [
            'trackers' => [
                ['id' => 1, 'name' => 'Tracker 1'],
                ['id' => 5, 'name' => 'Tracker 5'],
            ],
        ];
        $expectedReturn = [
            'Tracker 1' => 1,
            'Tracker 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/trackers.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    /**
     * Test getIdByName().
     *
     * @covers ::getIdByName
     * @test
     */
    public function testGetIdByNameMakesGetRequest()
    {
        // Test values
        $getResponse = [
            'trackers' => [
                ['id' => 5, 'name' => 'Tracker 5'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/trackers.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Tracker($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('Tracker 1'));
        $this->assertSame(5, $api->getIdByName('Tracker 5'));
    }
}

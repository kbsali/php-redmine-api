<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Role;

/**
 * @coversDefaultClass \Redmine\Api\Role
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class RoleTest extends \PHPUnit\Framework\TestCase
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
                $this->stringStartsWith('/roles.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Role($client);

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
                    $this->stringStartsWith('/roles.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Role($client);

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
            'roles' => [
                ['id' => 1, 'name' => 'Role 1'],
                ['id' => 5, 'name' => 'Role 5'],
            ],
        ];
        $expectedReturn = [
            'Role 1' => 1,
            'Role 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/roles.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Role($client);

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
            'roles' => [
                ['id' => 1, 'name' => 'Role 1'],
                ['id' => 5, 'name' => 'Role 5'],
            ],
        ];
        $expectedReturn = [
            'Role 1' => 1,
            'Role 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/roles.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Role($client);

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
            'roles' => [
                ['id' => 1, 'name' => 'Role 1'],
                ['id' => 5, 'name' => 'Role 5'],
            ],
        ];
        $expectedReturn = [
            'Role 1' => 1,
            'Role 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/roles.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Role($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowReturnsClientGetResponse()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith('/roles/5.json'))
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Role($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
    }
}

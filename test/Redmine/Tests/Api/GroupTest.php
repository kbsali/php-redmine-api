<?php

namespace Redmine\Tests\Api;

use Redmine\Api\Group;

/**
 * @coversDefaultClass Redmine\Api\Group
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class GroupTest extends \PHPUnit_Framework_TestCase
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
                $this->stringStartsWith('/groups.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

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
        $parameters = array('not-used');
        $getResponse = array('API Response');

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

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
        $getResponse = array(
            'groups' => array(
                array('id' => 1, 'name' => 'Group 1'),
                array('id' => 5, 'name' => 'Group 5'),
            ),
        );
        $expectedReturn = array(
            'Group 1' => 1,
            'Group 5' => 5,
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/groups.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

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
        $getResponse = array(
            'groups' => array(
                array('id' => 1, 'name' => 'Group 1'),
                array('id' => 5, 'name' => 'Group 5'),
            ),
        );
        $expectedReturn = array(
            'Group 1' => 1,
            'Group 5' => 5,
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/groups.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

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
        $getResponse = array(
            'groups' => array(
                array('id' => 1, 'name' => 'Group 1'),
                array('id' => 5, 'name' => 'Group 5'),
            ),
        );
        $expectedReturn = array(
            'Group 1' => 1,
            'Group 5' => 5,
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/groups.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

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
            ->with($this->stringStartsWith('/groups/5.json'))
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowCallsGetUrlWithParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $allParameters = array('not-used');

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups/5.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5, $allParameters));
    }

    /**
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveCallsDelete()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @test
     */
    public function testCreateCallsPost()
    {
        // Test values
        $getResponse = 'API Response';
        $postParameter = array(
            'name' => 'Group Name',
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                ),
                $this->stringContains('<group><name>Group Name</name></group>')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($postParameter));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @expectedException Exception
     * @test
     */
    public function testCreateThrowsExceptionIsNameIsMissing()
    {
        // Test values
        $postParameter = array();

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $api->create($postParameter);
    }

    /**
     * Test removeUser().
     *
     * @covers ::addUser
     * @covers ::post
     * @test
     */
    public function testAddUserCallsPost()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups/5/users'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                ),
                $this->stringContains('<user_id>10</user_id>')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->addUser(5, 10));
    }

    /**
     * Test removeUser().
     *
     * @covers ::delete
     * @covers ::removeUser
     * @test
     */
    public function testRemoveUserCallsDelete()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/groups/5/users/10'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->removeUser(5, 10));
    }
}

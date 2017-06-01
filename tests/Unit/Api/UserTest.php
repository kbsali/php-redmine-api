<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\User;

/**
 * @coversDefaultClass \Redmine\Api\User
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class UserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test getCurrentUser().
     *
     * @covers ::getCurrentUser
     * @test
     */
    public function testGetCurrentUserReturnsClientGetResponse()
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
                $this->logicalAnd(
                    $this->stringStartsWith('/users/current.json'),
                    $this->stringContains(urlencode('memberships,groups'))
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->getCurrentUser());
    }

    /**
     * Test getIdByUsername().
     *
     * @covers ::getIdByUsername
     * @test
     */
    public function testGetIdByUsernameMakesGetRequest()
    {
        // Test values
        $getResponse = [
            'users' => [
                ['id' => 5, 'login' => 'User 5'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertFalse($api->getIdByUsername('User 1'));
        $this->assertSame(5, $api->getIdByUsername('User 5'));
    }

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
            ->with('/users.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

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
        $parameters = [
            'offset' => 10,
            'limit' => 2,
        ];
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/users.json?'),
                    $this->stringContains('offset=10'),
                    $this->stringContains('limit=2')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($parameters));
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
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/users/5.json'),
                    $this->stringContains(urlencode('memberships,groups'))
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

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
    public function testShowReturnsClientGetResponseWithUniqueParameters()
    {
        // Test values
        $parameters = ['include' => ['parameter1', 'parameter2', 'memberships']];
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/users/5.json'),
                    $this->stringContains(urlencode('parameter1,parameter2,memberships,groups'))
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5, $parameters));
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
            ->with('/users/5.xml')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create());
    }

    /**
     * Test create().
     *
     * @covers            ::create
     * @dataProvider      incompleteCreateParameterProvider
     * @expectedException \Exception
     * @test
     *
     * @param array $parameters Parameters for create()
     */
    public function testCreateThrowsExceptionIfValueIsMissingInParameters($parameters)
    {
        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Provider for incomplete create parameters.
     *
     * @return array[]
     */
    public function incompleteCreateParameterProvider()
    {
        return [
            // Missing Login
            [
                [
                    'password' => 'secretPass',
                    'lastname' => 'Last Name',
                    'firstname' => 'Firstname',
                    'mail' => 'mail@example.com',
                ],
            ],
            // Missing last name
            [
                [
                    'login' => 'TestUser',
                    'password' => 'secretPass',
                    'firstname' => 'Firstname',
                    'mail' => 'mail@example.com',
                ],
            ],
            // Missing first name
            [
                [
                    'login' => 'TestUser',
                    'password' => 'secretPass',
                    'lastname' => 'Last Name',
                    'mail' => 'mail@example.com',
                ],
            ],
            // Missing email
            [
                [
                    'login' => 'TestUser',
                    'password' => 'secretPass',
                    'lastname' => 'Last Name',
                    'firstname' => 'Firstname',
                ],
            ],
        ];
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
        $parameters = [
            'login' => 'TestUser',
            'password' => 'secretPass',
            'lastname' => 'Last Name',
            'firstname' => 'Firstname',
            'mail' => 'mail@example.com',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/users.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<user>'),
                    $this->stringEndsWith('</user>'."\n"),
                    $this->stringContains('<login>TestUser</login>'),
                    $this->stringContains('<password>secretPass</password>'),
                    $this->stringContains('<lastname>Last Name</lastname>'),
                    $this->stringContains('<firstname>Firstname</firstname>'),
                    $this->stringContains('<mail>mail@example.com</mail>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @covers ::attachCustomFieldXML
     * @test
     */
    public function testCreateWithCustomField()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'login' => 'TestUser',
            'password' => 'secretPass',
            'lastname' => 'Last Name',
            'firstname' => 'Firstname',
            'mail' => 'mail@example.com',
            'custom_fields' => [
                ['id' => 5, 'value' => 'Value 5'],
                ['id' => 13, 'value' => 'Value 13', 'name' => 'CF Name'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/users.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<user>'),
                    $this->stringEndsWith('</user>'."\n"),
                    $this->stringContains('<login>TestUser</login>'),
                    $this->stringContains('<password>secretPass</password>'),
                    $this->stringContains('<lastname>Last Name</lastname>'),
                    $this->stringContains('<firstname>Firstname</firstname>'),
                    $this->stringContains('<mail>mail@example.com</mail>'),
                    $this->stringContains('<custom_fields type="array">'),
                    $this->stringContains('</custom_fields>'),
                    $this->stringContains('<custom_field name="CF Name" id="13">'),
                    $this->stringContains('<value>Value 13</value>'),
                    $this->stringContains('<custom_field id="5">'),
                    $this->stringContains('<value>Value 5</value>'),
                    $this->stringContains('</custom_field>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test update().
     *
     * @covers ::put
     * @covers ::update
     * @test
     */
    public function testUpdateCallsPut()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'mail' => 'user@example.com',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/users/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<user>'),
                    $this->stringEndsWith('</user>'."\n"),
                    $this->stringContains('<mail>user@example.com</mail>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::put
     * @covers ::update
     * @covers ::attachCustomFieldXML
     * @test
     */
    public function testUpdateWithCustomField()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'custom_fields' => [
                ['id' => 5, 'value' => 'Value 5'],
                ['id' => 13, 'value' => 'Value 13', 'name' => 'CF Name'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/users/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<user>'),
                    $this->stringEndsWith('</user>'."\n"),
                    $this->stringContains('<custom_fields type="array">'),
                    $this->stringContains('</custom_fields>'),
                    $this->stringContains('<custom_field name="CF Name" id="13">'),
                    $this->stringContains('<value>Value 13</value>'),
                    $this->stringContains('<custom_field id="5">'),
                    $this->stringContains('<value>Value 5</value>'),
                    $this->stringContains('</custom_field>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
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
            'users' => [
                ['id' => 1, 'login' => 'User 1'],
                ['id' => 5, 'login' => 'User 5'],
            ],
        ];
        $expectedReturn = [
            'User 1' => 1,
            'User 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

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
            'users' => [
                ['id' => 1, 'login' => 'User 1'],
                ['id' => 5, 'login' => 'User 5'],
            ],
        ];
        $expectedReturn = [
            'User 1' => 1,
            'User 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

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
            'users' => [
                ['id' => 1, 'login' => 'User 1'],
                ['id' => 5, 'login' => 'User 5'],
            ],
        ];
        $expectedReturn = [
            'User 1' => 1,
            'User 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }
}

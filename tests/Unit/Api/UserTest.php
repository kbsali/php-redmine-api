<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @coversDefaultClass \Redmine\Api\User
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class UserTest extends TestCase
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/users/current.json'),
                    $this->stringContains(urlencode('memberships,groups'))
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
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->getCurrentUser());
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
        $response = '{"users":[{"id":5,"login":"User 5"}]}';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new User(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\User::all()` is deprecated since v2.4.0, use `Redmine\Api\User::list()` instead.',
                    $errstr
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED
        );

        $api->all();
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @dataProvider getAllData
     * @test
     */
    public function testAllReturnsClientGetResponse($response, $responseType, $expectedResponse)
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/users.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all());
    }

    public static function getAllData(): array
    {
        return [
            'array response' => ['["API Response"]', 'application/json', ['API Response']],
            'string response' => ['"string"', 'application/json', 'Could not convert response body into array: "string"'],
            'false response' => ['', 'application/json', false],
        ];
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/users.json?'),
                    $this->stringContains('offset=10'),
                    $this->stringContains('limit=2')
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
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with('/users/5.xml')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
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
        $response = 'API Response';
        $parameters = [
            'mail' => 'user@example.com',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/users/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<user>'),
                    $this->stringEndsWith('</user>' . "\n"),
                    $this->stringContains('<mail>user@example.com</mail>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
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
        $response = 'API Response';
        $parameters = [
            'custom_fields' => [
                ['id' => 5, 'value' => 'Value 5'],
                ['id' => 13, 'value' => 'Value 13', 'name' => 'CF Name'],
            ],
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/users/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<user>'),
                    $this->stringEndsWith('</user>' . "\n"),
                    $this->stringContains('<custom_fields type="array">'),
                    $this->stringContains('</custom_fields>'),
                    $this->stringContains('<custom_field name="CF Name" id="13">'),
                    $this->stringContains('<value>Value 13</value>'),
                    $this->stringContains('<custom_field id="5">'),
                    $this->stringContains('<value>Value 5</value>'),
                    $this->stringContains('</custom_field>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
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
        $response = '{"users":[{"id":1,"login":"User 1"},{"id":5,"login":"User 5"}]}';
        $expectedReturn = [
            'User 1' => 1,
            'User 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
        $response = '{"users":[{"id":1,"login":"User 1"},{"id":5,"login":"User 5"}]}';
        $expectedReturn = [
            'User 1' => 1,
            'User 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
        $response = '{"users":[{"id":1,"login":"User 1"},{"id":5,"login":"User 5"}]}';
        $expectedReturn = [
            'User 1' => 1,
            'User 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/users.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }
}

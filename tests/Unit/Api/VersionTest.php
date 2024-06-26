<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Client\Client;
use Redmine\Exception\InvalidParameterException;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new Version(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Version::all()` is deprecated since v2.4.0, use `Redmine\Api\Version::listByProject()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->all(5);
    }

    /**
     * Test all().
     *
     * @dataProvider getAllData
     */
    #[DataProvider('getAllData')]
    public function testAllReturnsClientGetResponse($response, $responseType, $expectedResponse)
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all(5));
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
        $client->expects($this->any())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/versions.json'),
                    $this->stringContains('offset=10'),
                    $this->stringContains('limit=2'),
                ),
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all(5, $parameters));
    }

    /**
     * Test listing().
     */
    public function testListingReturnsNameIdArray()
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     */
    public function testListingReturnsIdNameIfReverseIsFalseArray()
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            1 => 'Version 1',
            5 => 'Version 5',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, false, false));
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetOnlyTheFirstTime()
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with('/projects/5/versions.json')
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, true));
        $this->assertSame($expectedReturn, $api->listing(5, true));
    }

    /**
     * Test getIdByName().
     */
    public function testGetIdByNameMakesGetRequest()
    {
        // Test values
        $response = '{"versions":[{"id":5,"name":"Version 5"}]}';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/versions.json'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName(5, 'Version 1'));
        $this->assertSame(5, $api->getIdByName(5, 'Version 5'));
    }

    /**
     * Test validateSharing().
     *
     * @dataProvider      invalidSharingProvider
     *
     *
     * @param string $sharingValue
     */
    #[DataProvider('invalidSharingProvider')]
    public function testCreateThrowsExceptionWithInvalidSharing($sharingValue)
    {
        // Test values
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Version($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for sharing are: none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->create('test', $parameters);
    }

    /**
     * Data provider for invalid sharing values.
     *
     * @return array[]
     */
    public static function invalidSharingProvider(): array
    {
        return [
            ['all'],
            ['invalid'],
        ];
    }
}

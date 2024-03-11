<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Client\Client;
use Redmine\Exception\InvalidParameterException;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @coversDefaultClass \Redmine\Api\Version
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class VersionTest extends TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new Version(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Version::all()` is deprecated since v2.4.0, use `Redmine\Api\Version::listByProject()` instead.',
                    $errstr
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED
        );

        $api->all(5);
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @dataProvider getAllData
     * @test
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
        $client->expects($this->any())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/versions.json'),
                    $this->stringContains('offset=10'),
                    $this->stringContains('limit=2')
                )
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
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveWithNumericIdCallsDelete()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with('/versions/5.xml')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
    }

    /**
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveWithStringCallsDelete()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with('/versions/5.xml')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @covers ::validateStatus
     *
     * @test
     */
    public function testUpdateThrowsExceptionWithInvalidStatus()
    {
        // Test values
        $parameters = [
            'description' => 'Test version description',
            'status' => 'invalid',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Version($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for status : open, locked, closed');

        // Perform the tests
        $api->update(5, $parameters);
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
            'name' => 'Test version',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/versions/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<version>'),
                    $this->stringEndsWith('</version>' . "\n"),
                    $this->stringContains('<name>Test version</name>')
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @covers ::put
     * @covers ::validateStatus
     * @test
     */
    public function testUpdateWithValidStatusCallsPut()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'status' => 'locked',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/versions/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<version>'),
                    $this->stringEndsWith('</version>' . "\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->stringContains('<status>locked</status>')
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Version($client);

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
     *
     * @covers ::listing
     * @test
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
     *
     * @covers ::listing
     * @test
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
     *
     * @covers ::listing
     * @test
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
     *
     * @covers ::getIdByName
     * @test
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
                $this->stringStartsWith('/projects/5/versions.json')
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
     * @covers            ::create
     * @covers            ::validateSharing
     * @dataProvider      invalidSharingProvider
     *
     * @test
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
        $this->expectExceptionMessage('Possible values for sharing : none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->create('test', $parameters);
    }

    /**
     * Test validateSharing().
     *
     * @covers       ::update
     * @covers       ::validateSharing
     * @dataProvider validSharingProvider
     * @test
     *
     * @param string $sharingValue
     * @param string $sharingXmlElement
     */
    #[DataProvider('validSharingProvider')]
    public function testUpdateWithValidSharing($sharingValue, $sharingXmlElement)
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Update the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/versions/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<version>'),
                    $this->stringEndsWith('</version>' . "\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->stringContains($sharingXmlElement)
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Update the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
    }

    /**
     * Test validateSharing().
     *
     * @covers       ::update
     * @covers       ::validateSharing
     * @dataProvider validEmptySharingProvider
     * @test
     *
     * @param string $sharingValue
     */
    #[DataProvider('validEmptySharingProvider')]
    public function testUpdateWithValidEmptySharing($sharingValue)
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Update the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/versions/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<version>'),
                    $this->stringEndsWith('</version>' . "\n"),
                    $this->stringContains('<name>Test version</name>'),
                    $this->logicalNot(
                        $this->stringContains('<sharing')
                    )
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Update the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
    }

    /**
     * Test validateSharing().
     *
     * @covers            ::update
     * @covers            ::validateSharing
     * @dataProvider      invalidSharingProvider
     *
     * @test
     *
     * @param string $sharingValue
     */
    #[DataProvider('invalidSharingProvider')]
    public function testUpdateThrowsExceptionWithInvalidSharing($sharingValue)
    {
        // Test values
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Update the used mock objects
        $client = $this->createMock(Client::class);

        // Update the object under test
        $api = new Version($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for sharing : none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->update(5, $parameters);
    }

    /**
     * Data provider for valid sharing values.
     *
     * @return array[]
     */
    public static function validSharingProvider(): array
    {
        return [
            ['none', '<sharing>none</sharing>'],
            ['descendants', '<sharing>descendants</sharing>'],
            ['hierarchy', '<sharing>hierarchy</sharing>'],
            ['tree', '<sharing>tree</sharing>'],
            ['system', '<sharing>system</sharing>'],
        ];
    }

    /**
     * Data provider for valid empty sharing values.
     *
     * @return array[]
     */
    public static function validEmptySharingProvider(): array
    {
        return [
            [null],
            [false],
            [''],
        ];
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

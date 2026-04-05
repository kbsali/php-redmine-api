<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Exception\InvalidParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Version` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Version {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Version::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Version::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Version($this->createStub(HttpClient::class));
    }

    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = Version::fromHttpClient($this->createStub(HttpClient::class));

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
     *
     * @param mixed $expectedResponse
     */
    #[DataProvider('getAllData')]
    public function testAllReturnsClientGetResponse(string $response, string $responseType, $expectedResponse): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                $responseType,
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all(5));
    }

    /**
     * @return array<array<mixed>>
     */
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
    public function testAllReturnsClientGetResponseWithParameters(): void
    {
        // Test values
        $parameters = [
            'offset' => 10,
            'limit' => 2,
        ];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json?limit=2&offset=10',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all(5, $parameters));
    }

    /**
     * Test listing().
     */
    public function testListingReturnsNameIdArray(): void
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     */
    public function testListingReturnsIdNameIfReverseIsFalseArray(): void
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            1 => 'Version 1',
            5 => 'Version 5',
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, false, false));
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetOnlyTheFirstTime(): void
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetEveryTimeWithForceUpdate(): void
    {
        // Test values
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';
        $expectedReturn = [
            'Version 1' => 1,
            'Version 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, true));
        $this->assertSame($expectedReturn, $api->listing(5, true));
    }

    /**
     * Test listing().
     */
    public function testListingTriggersDeprecationWarning(): void
    {
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = Version::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Version::listing()` is deprecated since v2.7.0, use `Redmine\Api\Version::listNamesByProject()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->listing(5);
    }

    /**
     * Test getIdByName().
     */
    public function testGetIdByNameMakesGetRequest(): void
    {
        // Test values
        $response = '{"versions":[{"id":5,"name":"Version 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );


        // Create the object under test
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName(5, 'Version 1'));
        $this->assertSame(5, $api->getIdByName(5, 'Version 5'));
    }

    public function testGetIdByNameTriggersDeprecationWarning(): void
    {
        $response = '{"versions":[{"id":1,"name":"Version 1"},{"id":5,"name":"Version 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = Version::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Version::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\Version::listNamesByProject()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName(5, 'Version 5');
    }

    /**
     * Test validateSharing().
     *
     * @dataProvider      invalidSharingProvider
     */
    #[DataProvider('invalidSharingProvider')]
    public function testCreateThrowsExceptionWithInvalidSharing(string $sharingValue): void
    {
        // Test values
        $parameters = [
            'name' => 'Test version',
            'sharing' => $sharingValue,
        ];

        // Create the used mock objects
        $client = $this->createStub(HttpClient::class);

        // Create the object under test
        $api = Version::fromHttpClient($client);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Possible values for sharing are: none, descendants, hierarchy, tree, system');

        // Perform the tests
        $api->create('test', $parameters);
    }

    /**
     * Data provider for invalid sharing values.
     *
     * @return array<array<mixed>>
     */
    public static function invalidSharingProvider(): array
    {
        return [
            ['all'],
            ['invalid'],
        ];
    }
}

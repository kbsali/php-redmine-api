<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Group::class)]
class GroupTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Group` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Group {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Group::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Group::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Group($this->createStub(HttpClient::class));
    }

    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = Group::fromHttpClient($this->createStub(HttpClient::class));

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Group::all()` is deprecated since v2.4.0, use `Redmine\Api\Group::list()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->all();
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
                '/groups.json',
                'application/json',
                '',
                200,
                $responseType,
                $response,
            ],
        );

        // Create the object under test
        $api = Group::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all());
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
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/groups.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Group::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    /**
     * Test listing().
     */
    public function testListingTriggersDeprecationWarning(): void
    {
        $response = '{"groups":[{"id":1,"name":"Group 1"},{"id":5,"name":"Group 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/groups.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = Group::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Group::listing()` is deprecated since v2.7.0, use `Redmine\Api\Group::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->listing();
    }

    /**
     * Test listing().
     */
    public function testListingReturnsNameIdArray(): void
    {
        // Test values
        $response = '{"groups":[{"id":1,"name":"Group 1"},{"id":5,"name":"Group 5"}]}';
        $expectedReturn = [
            'Group 1' => 1,
            'Group 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/groups.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Group::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetOnlyTheFirstTime(): void
    {
        // Test values
        $response = '{"groups":[{"id":1,"name":"Group 1"},{"id":5,"name":"Group 5"}]}';
        $expectedReturn = [
            'Group 1' => 1,
            'Group 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/groups.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Group::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetEveryTimeWithForceUpdate(): void
    {
        // Test values
        $response = '{"groups":[{"id":1,"name":"Group 1"},{"id":5,"name":"Group 5"}]}';
        $expectedReturn = [
            'Group 1' => 1,
            'Group 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/groups.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/groups.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Group::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        // @phpstan-ignore argument.type(Test casting int to bool)
        $this->assertSame($expectedReturn, $api->listing(1));
    }
}

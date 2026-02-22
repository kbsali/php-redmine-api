<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntryActivity;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(TimeEntryActivity::class)]
class TimeEntryActivityTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\TimeEntryActivity` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends TimeEntryActivity {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\TimeEntryActivity::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\TimeEntryActivity::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new TimeEntryActivity($this->createStub(HttpClient::class));
    }

    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = TimeEntryActivity::fromHttpClient($this->createStub(HttpClient::class));

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\TimeEntryActivity::all()` is deprecated since v2.4.0, use `Redmine\Api\TimeEntryActivity::list()` instead.',
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
     */
    #[DataProvider('getAllData')]
    public function testAllReturnsClientGetResponse(string $response, string $responseType, $expectedResponse): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                $responseType,
                $response,
            ],
        );

        // Create the object under test
        $api = TimeEntryActivity::fromHttpClient($client);

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
                '/enumerations/time_entry_activities.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = TimeEntryActivity::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    public function testListingReturnsNameIdArray(): void
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivities 1"},{"id":2,"name":"TimeEntryActivities 2"}]}';
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = TimeEntryActivity::fromHttpClient($client);

        $this->assertSame($expectedReturn, $api->listing());
    }

    public function testListingCallsGetEveryTimeWithForceUpdate(): void
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivities 1"},{"id":2,"name":"TimeEntryActivities 2"}]}';
        $expectedReturn = [
            'TimeEntryActivities 1' => 1,
            'TimeEntryActivities 2' => 2,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = TimeEntryActivity::fromHttpClient($client);

        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    /**
     * Test listing().
     */
    public function testListingTriggersDeprecationWarning(): void
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivity 1"},{"id":5,"name":"TimeEntryActivity 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = TimeEntryActivity::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\TimeEntryActivity::listing()` is deprecated since v2.7.0, use `Redmine\Api\TimeEntryActivity::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->listing();
    }

    public function testGetIdByNameMakesGetRequest(): void
    {
        $response = '{"time_entry_activities":[{"id":2,"name":"TimeEntryActivities 2"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = TimeEntryActivity::fromHttpClient($client);

        $this->assertFalse($api->getIdByName('TimeEntryActivities 1'));
        $this->assertSame(2, $api->getIdByName('TimeEntryActivities 2'));
    }

    public function testGetIdByNameTriggersDeprecationWarning(): void
    {
        $response = '{"time_entry_activities":[{"id":1,"name":"TimeEntryActivity 1"},{"id":5,"name":"TimeEntryActivity 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/time_entry_activities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = TimeEntryActivity::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\TimeEntryActivity::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\TimeEntryActivity::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName('TimeEntryActivities 2');
    }
}

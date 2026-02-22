<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(CustomField::class)]
class CustomFieldTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\CustomField` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends CustomField {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\CustomField::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\CustomField::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new CustomField($this->createStub(HttpClient::class));
    }

    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = CustomField::fromHttpClient($this->createStub(HttpClient::class));

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\CustomField::all()` is deprecated since v2.4.0, use `Redmine\Api\CustomField::list()` instead.',
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
                '/custom_fields.json',
                'application/json',
                '',
                200,
                $responseType,
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

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
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all($allParameters));
    }

    /**
     * Test all().
     */
    public function testAllReturnsClientGetResponseWithHighLimit(): void
    {
        // Test values
        $response = '{"limit":"100","items":[]}';
        $allParameters = ['limit' => 250];
        $expectedResponse = [
            'limit' => ['100', '100', '100'], // TODO: Check response created by array_merge_recursive()
            'items' => [],
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/custom_fields.json?limit=100&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/custom_fields.json?limit=50&offset=200',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all($allParameters));
    }

    /**
     * Test all().
     */
    public function testAllCallsEndpointUntilOffsetIsHigherThanTotalCount(): void
    {
        // Test values
        $response = '{"limit":"100","offset":"10","total_count":"5","items":[]}';
        $allParameters = ['limit' => 250];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $retrievedDataSet = $api->all($allParameters);
        $this->assertIsArray($retrievedDataSet);
        $this->assertArrayHasKey('limit', $retrievedDataSet);
        $this->assertArrayHasKey('items', $retrievedDataSet);
    }

    /**
     * Test listing().
     */
    public function testListingReturnsNameIdArray(): void
    {
        // Test values
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetOnlyTheFirstTime(): void
    {
        // Test values
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

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
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    /**
     * Test listing().
     */
    public function testListingTriggersDeprecationWarning(): void
    {
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = CustomField::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\CustomField::listing()` is deprecated since v2.7.0, use `Redmine\Api\CustomField::listNames()` instead.',
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
     * Test getIdByName().
     */
    public function testGetIdByNameMakesGetRequest(): void
    {
        // Test values
        $response = '{"custom_fields":[{"id":5,"name":"CustomField 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('CustomField 1'));
        $this->assertSame(5, $api->getIdByName('CustomField 5'));
    }

    public function testGetIdByNameTriggersDeprecationWarning(): void
    {
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = CustomField::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\CustomField::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\CustomField::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName('CustomField 5');
    }
}

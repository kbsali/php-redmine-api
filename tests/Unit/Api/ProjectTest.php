<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use ReflectionMethod;
use SimpleXMLElement;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Project::class)]
class ProjectTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Project` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Project {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Project::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Project::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Project($this->createStub(HttpClient::class));
    }

    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = Project::fromHttpClient($this->createStub(HttpClient::class));

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Project::all()` is deprecated since v2.4.0, use `Redmine\Api\Project::list()` instead.',
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
                '/projects.json',
                'application/json',
                '',
                200,
                $responseType,
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

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
                '/projects.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    /**
     * Test listing().
     */
    public function testListingReturnsNameIdArray(): void
    {
        // Test values
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetOnlyTheFirstTime(): void
    {
        // Test values
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

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
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    /**
     * Test listing().
     */
    public function testListingTriggersDeprecationWarning(): void
    {
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = Project::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Project::listing()` is deprecated since v2.7.0, use `Redmine\Api\Project::listNames()` instead.',
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
        $response = '{"projects":[{"id":5,"name":"Project 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('Project 1'));
        $this->assertSame(5, $api->getIdByName('Project 5'));
    }

    public function testGetIdByNameTriggersDeprecationWarning(): void
    {
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        $api = Project::fromHttpClient($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Project::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\Project::listNames()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName('Project 1');
    }

    public function testDeprecatedPrepareParamsXml(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = Project::fromHttpClient($client);

        $method = new ReflectionMethod($api, 'prepareParamsXml');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertInstanceOf(SimpleXMLElement::class, $method->invoke($api, ['id' => 1]));
    }
}

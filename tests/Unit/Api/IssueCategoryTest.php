<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(IssueCategory::class)]
class IssueCategoryTest extends TestCase
{
    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = new IssueCategory(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\IssueCategory::all()` is deprecated since v2.4.0, use `Redmine\Api\IssueCategory::listByProject()` instead.',
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
     * @dataProvider getAllDAta
     */
    #[DataProvider('getAllData')]
    public function testAllReturnsClientGetResponseWithProject($response, $responseType, $expectedResponse): void
    {
        // Test values
        $projectId = 5;

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/projects/5/issue_categories.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all($projectId));
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
    public function testAllReturnsClientGetResponseWithParametersAndProject(): void
    {
        // Test values
        $projectId = 5;
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/issue_categories.json'),
                    $this->stringContains('not-used'),
                ),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($projectId, $parameters));
    }

    /**
     * Test listing().
     */
    public function testListingReturnsNameIdArray(): void
    {
        // Test values
        $response = '{"issue_categories":[{"id":1,"name":"IssueCategory 1"},{"id":5,"name":"IssueCategory 5"}]}';
        $expectedReturn = [
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
    }

    /**
     * Test listing().
     */
    public function testListingCallsGetOnlyTheFirstTime(): void
    {
        // Test values
        $response = '{"issue_categories":[{"id":1,"name":"IssueCategory 1"},{"id":5,"name":"IssueCategory 5"}]}';
        $expectedReturn = [
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueCategory($client);

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
        $response = '{"issue_categories":[{"id":1,"name":"IssueCategory 1"},{"id":5,"name":"IssueCategory 5"}]}';
        $expectedReturn = [
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, true));
        $this->assertSame($expectedReturn, $api->listing(5, true));
    }

    /**
     * Test listing().
     */
    public function testListingTriggersDeprecationWarning(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('requestGet')
            ->willReturn(true);
        $client->method('getLastResponseBody')
            ->willReturn('{"issue_categories":[{"id":1,"name":"IssueCategory 1"},{"id":5,"name":"IssueCategory 5"}]}');
        $client->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new IssueCategory($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\IssueCategory::listing()` is deprecated since v2.7.0, use `Redmine\Api\IssueCategory::listNamesByProject()` instead.',
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
        $response = '{"issue_categories":[{"id":5,"name":"IssueCategory 5"}]}';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json'),
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName(5, 'IssueCategory 1'));
        $this->assertSame(5, $api->getIdByName(5, 'IssueCategory 5'));
    }

    public function testGetIdByNameTriggersDeprecationWarning(): void
    {
        $client = $this->createMock(Client::class);
        $client->method('requestGet')
            ->willReturn(true);
        $client->method('getLastResponseBody')
            ->willReturn('{"issue_categories":[{"id":1,"name":"IssueCategory 1"},{"id":5,"name":"IssueCategory 5"}]}');
        $client->method('getLastResponseContentType')
            ->willReturn('application/json');

        $api = new IssueCategory($client);

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\IssueCategory::getIdByName()` is deprecated since v2.7.0, use `Redmine\Api\IssueCategory::listNamesByProject()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->getIdByName(5, 'Category Name');
    }
}

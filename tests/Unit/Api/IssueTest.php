<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Api\IssueCategory;
use Redmine\Api\IssueStatus;
use Redmine\Api\Project;
use Redmine\Api\Tracker;
use Redmine\Api\User;
use Redmine\Client\Client;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\MockClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Issue::class)]
class IssueTest extends TestCase
{
    public static function getPriorityConstantsData(): array
    {
        return [
            [1, Issue::PRIO_LOW],
            [2, Issue::PRIO_NORMAL],
            [3, Issue::PRIO_HIGH],
            [4, Issue::PRIO_URGENT],
            [5, Issue::PRIO_IMMEDIATE],
        ];
    }

    /**
     * Test the constants.
     *
     * @dataProvider getPriorityConstantsData
     */
    #[DataProvider('getPriorityConstantsData')]
    public function testPriorityConstants($expected, $value): void
    {
        $this->assertSame($expected, $value);
    }

    /**
     * Test all().
     */
    public function testAllTriggersDeprecationWarning(): void
    {
        $api = new Issue(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Issue::all()` is deprecated since v2.4.0, use `Redmine\Api\Issue::list()` instead.',
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
    public function testAllReturnsClientGetResponse($response, $responseType, $expectedResponse): void
    {
        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(1))
            ->method('requestGet')
            ->with('/issues.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new Issue($client);

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

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues.json'),
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
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    /**
     * Test cleanParams() with Client for BC
     */
    public function testCreateWithClientCleansParameters(): void
    {
        // Test values
        $response = '<?xml version="1.0"?><issue></issue>';
        $parameters = [
            'project' => 'Project 1 Name',
            'category' => 'Category 5 Name',
            'status' => 'Status 6 Name',
            'tracker' => 'Tracker 2 Name',
            'assigned_to' => 'user_3',
            'author' => 'user_4',
        ];

        // Create the used mock objects
        $httpClient = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                '{"projects":[{"id":1,"name":"Project 1 Name"},{"id":2,"name":"Project 1 Name"}]}',
            ],
            [
                'GET',
                '/projects/1/issue_categories.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"issue_categories":[{"id":5,"name":"Category 5 Name"}]}',
            ],
            [
                'GET',
                '/issue_statuses.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"issue_statuses":[{"id":6,"name":"Status 6 Name"}]}',
            ],
            [
                'GET',
                '/trackers.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"trackers":[{"id":2,"name":"Tracker 2 Name"}]}',
            ],
            [
                'GET',
                '/users.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                '{"users":[{"id":3,"login":"user_3"},{"id":4,"login":"user_4"}]}',
            ],
        );

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(5))
            ->method('getApi')
            ->willReturnMap(
                [
                    ['project', new Project($httpClient)],
                    ['issue_category', new IssueCategory($httpClient)],
                    ['issue_status', new IssueStatus($httpClient)],
                    ['tracker', new Tracker($httpClient)],
                    ['user', new User($httpClient)],
                ],
            )
        ;

        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/issues.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue><project_id>1</project_id><category_id>5</category_id><status_id>6</status_id><tracker_id>2</tracker_id><assigned_to_id>3</assigned_to_id><author_id>4</author_id></issue>

                XML,
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/xml');

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertXmlStringEqualsXmlString($response, $api->create($parameters)->asXML());
    }
}

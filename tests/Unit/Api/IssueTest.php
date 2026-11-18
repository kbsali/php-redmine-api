<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Api\IssueCategory;
use Redmine\Api\IssueStatus;
use Redmine\Api\Project;
use Redmine\Api\Tracker;
use Redmine\Api\User;
use Redmine\Client\Client;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Http\Request;
use Redmine\Http\Response;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
#[CoversClass(Issue::class)]
class IssueTest extends TestCase
{
    public function testExtendingTheClassTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Class `Redmine\Api\Issue` will declared as final in v3.0.0, stop extending it.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new class ($this->createStub(HttpClient::class)) extends Issue {};
    }

    public function testConstructorTriggersDeprecationWarning(): void
    {
        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Method `Redmine\Api\Issue::__construct()` is deprecated since v2.9.0 and will declared as private in v3.0.0, use `Redmine\Api\Issue::fromHttpClient()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        new Issue($this->createStub(HttpClient::class));
    }

    /**
     * Test cleanParams() with a Client that is also a HttpClient
     */
    public function testCreateWithClientAndHttpClientCleansParameters(): void
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

        $client = AssertingHttpClient::create(
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

        /** @var Client&HttpClient&\PHPUnit\Framework\MockObject\MockObject $legacyClient */
        $legacyClient = $this->createMockForIntersectionOfInterfaces([Client::class, HttpClient::class]);
        $legacyClient->expects($this->exactly(5))
            ->method('getApi')
            ->willReturnMap(
                [
                    ['project', Project::fromHttpClient($client)],
                    ['issue_category', IssueCategory::fromHttpClient($client)],
                    ['issue_status', IssueStatus::fromHttpClient($client)],
                    ['tracker', Tracker::fromHttpClient($client)],
                    ['user', User::fromHttpClient($client)],
                ],
            )
        ;

        $legacyClient->expects($this->once())
            ->method('request')
            ->willReturnCallback(function (Request $request) use ($response): Response {
                $this->assertSame('POST', $request->getMethod());
                $this->assertSame('/issues.xml', $request->getPath());
                $this->assertSame(
                    <<< XML
                    <?xml version="1.0"?>
                    <issue><project_id>1</project_id><category_id>5</category_id><status_id>6</status_id><tracker_id>2</tracker_id><assigned_to_id>3</assigned_to_id><author_id>4</author_id></issue>

                    XML,
                    $request->getContent(),
                );

                return HttpFactory::makeResponse(201, 'application/xml', $response);
            });

        // Create the object under test
        $api = new Issue($legacyClient);

        // Perform the tests
        $this->assertXmlStringEqualsXmlString($response, $api->create($parameters)->asXML());
    }
}

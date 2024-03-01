<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\MockClient;
use SimpleXMLElement;

/**
 * @coversDefaultClass \Redmine\Api\Issue
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
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
     *
     * @test
     */
    public function testPriorityConstants($expected, $value)
    {
        $this->assertSame($expected, $value);
    }

    /**
     * Test all().
     *
     * @covers ::all
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new Issue(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Issue::all()` is deprecated since v2.4.0, use `Redmine\Api\Issue::list()` instead.',
                    $errstr
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED
        );

        $api->all();
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @dataProvider getAllData
     * @test
     */
    public function testAllReturnsClientGetResponse($response, $responseType, $expectedResponse)
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
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParameters()
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
                    $this->stringContains('not-used')
                )
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
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     */
    public function testRemoveCallsDelete()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
    }

    /**
     * Test removeWatcher().
     *
     * @covers ::removeWatcher
     * @test
     */
    public function testRemoveWatcherCallsPost()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->stringStartsWith('/issues/5/watchers/10.xml')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->removeWatcher(5, 10));
    }

    /**
     * Test cleanParams() with Client for BC
     *
     * @covers ::create
     * @covers ::cleanParams
     * @covers ::getIssueCategoryApi
     * @covers ::getIssueStatusApi
     * @covers ::getProjectApi
     * @covers ::getTrackerApi
     * @covers ::getUserApi
     * @test
     */
    public function testCreateWithClientCleansParameters()
    {
        // Test values
        $response = '<?xml version="1.0"?><issue></issue>';
        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        ];

        // Create the used mock objects
        $getIdByNameApi = $this->createMock('Redmine\Api\Project');
        $getIdByNameApi->expects($this->exactly(3))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $issueCategoryGetIdByNameApi = $this->createMock('Redmine\Api\IssueCategory');
        $issueCategoryGetIdByNameApi->expects($this->exactly(1))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $getIdByUsernameApi = $this->createMock('Redmine\Api\User');
        $getIdByUsernameApi->expects($this->exactly(2))
            ->method('getIdByUsername')
            ->willReturn('cleanedValue');

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(5))
            ->method('getApi')
            ->willReturnMap(
                [
                    ['project', $getIdByNameApi],
                    ['issue_category', $issueCategoryGetIdByNameApi],
                    ['issue_status', $getIdByNameApi],
                    ['tracker', $getIdByNameApi],
                    ['user', $getIdByUsernameApi],
                ]
            );

        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<issue>'),
                    $this->stringEndsWith('</issue>' . "\n"),
                    $this->stringContains('<project_id>cleanedValue</project_id>'),
                    $this->stringContains('<category_id>cleanedValue</category_id>'),
                    $this->stringContains('<status_id>cleanedValue</status_id>'),
                    $this->stringContains('<tracker_id>cleanedValue</tracker_id>'),
                    $this->stringContains('<assigned_to_id>cleanedValue</assigned_to_id>'),
                    $this->stringContains('<author_id>cleanedValue</author_id>')
                )
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

<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;

/**
 * @coversDefaultClass \Redmine\Api\IssueCategory
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssueCategoryTest extends TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithProject()
    {
        // Test values
        $projectId = 5;
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json')
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
        $this->assertSame($expectedReturn, $api->all($projectId));
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParametersAndProject()
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
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($projectId, $parameters));
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
                $this->stringStartsWith('/projects/5/issue_categories')
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
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetOnlyTheFirstTime()
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
                $this->stringStartsWith('/projects/5/issue_categories')
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
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetEveryTimeWithForceUpdate()
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
                $this->stringStartsWith('/projects/5/issue_categories')
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
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowReturnsClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/issue_categories/5.json'))
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($response, $api->show(5));
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
                $this->logicalXor(
                    $this->stringStartsWith('/issue_categories/5.json'),
                    $this->stringStartsWith('/issue_categories/5.xml')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueCategory($client);

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
    public function testRemoveCallsDeleteWithParameters()
    {
        // Test values
        $response = 'API Response';
        $parameters = ['not-used'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issue_categories/5'),
                    $this->logicalXor(
                        $this->stringContains('.json?'),
                        $this->stringContains('.xml?')
                    ),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5, $parameters));
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
        $response = '{"issue_categories":[{"id":5,"name":"IssueCategory 5"}]}';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json')
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

    /**
     * Test create().
     *
     * @covers ::post
     * @covers ::create
     * @test
     */
    public function testCreateThrowsExceptionIfNameIsMissing()
    {
        // Test values
        $parameters = [
            'name' => null,
            'assigned_to_id' => 2,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new IssueCategory($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`');

        // Perform the tests
        $api->create(5, $parameters);
    }

    /**
     * Test create().
     *
     * @covers ::post
     * @covers ::create
     * @test
     */
    public function testCreateCallsPost()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'name' => 'Test Category',
            'assigned_to_id' => 2,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/projects/5/issue_categories.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue_category>'),
                    $this->stringEndsWith('</issue_category>'."\n"),
                    $this->stringContains('<name>Test Category</name>'),
                    $this->stringContains('<assigned_to_id>2</assigned_to_id>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($response, $api->create(5, $parameters));
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
            'name' => 'Test Category',
            'assigned_to_id' => 2,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/issue_categories/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue_category>'),
                    $this->stringEndsWith('</issue_category>'."\n"),
                    $this->stringContains('<name>Test Category</name>'),
                    $this->stringContains('<assigned_to_id>2</assigned_to_id>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
    }
}

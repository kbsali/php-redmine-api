<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntry;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;

/**
 * @coversDefaultClass \Redmine\Api\TimeEntry
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class TimeEntryTest extends TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponse()
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/time_entries.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all());
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
        $parameters = [
            'project_id' => 5,
            'user_id' => 10,
            'limit' => 2,
        ];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/time_entries.json?'),
                    $this->stringContains('project_id=5'),
                    $this->stringContains('user_id=10'),
                    $this->stringContains('limit=2')
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
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/time_entries/5.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show(5));
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
            ->with('/time_entries/5.xml')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
    }

    /**
     * Test create().
     *
     * @covers ::create
     *
     * @test
     */
    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new TimeEntry($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');

        // Perform the tests
        $this->assertSame($response, $api->create(['id' => 5]));
    }

    /**
     * Test create().
     *
     * @covers ::create
     *
     * @test
     */
    public function testCreateThrowsExceptionIfIssueIdAndProjectIdAreMissingInParameters()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'hours' => '5.25',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new TimeEntry($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');

        // Perform the tests
        $this->assertSame($response, $api->create($parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     *
     * @test
     */
    public function testCreateThrowsExceptionIfHoursAreMissingInParameters()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'issue_id' => '15',
            'project_id' => '25',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new TimeEntry($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `issue_id` or `project_id`, `hours`');

        // Perform the tests
        $this->assertSame($response, $api->create($parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @test
     */
    public function testCreateCallsPost()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'issue_id' => '15',
            'project_id' => '25',
            'hours' => '5.25',
            'comments' => 'some text with xml entities: & < > " \' ',
            'custom_fields' => [
                [
                    'id' => 1,
                    'name' => 'Affected version',
                    'value' => '1.0.1',
                ],
                [
                    'id' => 2,
                    'name' => 'Resolution',
                    'value' => 'Fixed',
                ],
            ],
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/time_entries.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<time_entry>'),
                    $this->stringEndsWith('</time_entry>'."\n"),
                    $this->stringContains('<issue_id>15</issue_id>'),
                    $this->stringContains('<project_id>25</project_id>'),
                    $this->stringContains('<hours>5.25</hours>'),
                    $this->stringContains('<comments>some text with xml entities: &amp; &lt; &gt; " \' </comments>'),
                    $this->stringContains('<custom_fields type="array"><custom_field name="Affected version" id="1"><value>1.0.1</value></custom_field><custom_field name="Resolution" id="2"><value>Fixed</value></custom_field></custom_fields>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($response, $api->create($parameters));
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
            'hours' => '10.25',
            'comments' => 'some text with xml entities: & < > " \' ',
            'custom_fields' => [
                [
                    'id' => 1,
                    'name' => 'Affected version',
                    'value' => '1.0.1',
                ],
                [
                    'id' => 2,
                    'name' => 'Resolution',
                    'value' => 'Fixed',
                ],
            ],
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/time_entries/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<time_entry>'),
                    $this->stringEndsWith('</time_entry>'."\n"),
                    $this->stringContains('<hours>10.25</hours>'),
                    $this->stringContains('<custom_fields type="array"><custom_field name="Affected version" id="1"><value>1.0.1</value></custom_field><custom_field name="Resolution" id="2"><value>Fixed</value></custom_field></custom_fields>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new TimeEntry($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
    }
}

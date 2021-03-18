<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\IssueRelation
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssueRelationTest extends TestCase
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/issues/5/relations.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($response, $api->all(5));
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
        $parameters = ['not-used'];
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5/relations.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame([$response], $api->all(5, $parameters));
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
        $response = '{"relation":{"child":[5,2,3]}}';
        $returnValue = [
            'child' => [5, 2, 3],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/relations/5.json'))
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($returnValue, $api->show(5));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowReturnsArrayIfNull()
    {
        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/relations/5.json'))
            ->willReturn(false);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn('');

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame([], $api->show(5));
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
        $client = $this->getMockBuilder(Client::class)
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/relations/5'),
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
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
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
        $response = '{"test":"response"}';
        $responseArray = ['test' => 'response'];
        $parameters = [];

        // Create the used mock objects
        $client = $this->getMockBuilder(Client::class)
                        ->disableOriginalConstructor()
                        ->getMock();
        $client->expects($this->once())
                ->method('requestPost')
                ->with(
                    '/issues/1/relations.json',
                    json_encode([
                        'relation' => [
                            'relation_type' => 'relates',
                        ],
                    ])
                )
                ->willReturn(true);
            $client->expects($this->exactly(1))
                ->method('getLastResponseBody')
                ->willReturn($response);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($responseArray, $api->create(1, $parameters));
    }
}

<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\IssueRelation;

/**
 * @coversDefaultClass \Redmine\Api\IssueRelation
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssueRelationTest extends \PHPUnit\Framework\TestCase
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/issues/5/relations.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all(5));
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
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5/relations.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all(5, $parameters));
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
        $returnValue = [
            'child' => [5, 2, 3],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith('/relations/5.json'))
            ->willReturn(['relation' => $returnValue]);

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
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith('/relations/5.json'))
            ->willReturn(null);

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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/relations/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
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
        $responseArray = ['test' => 'response'];
        $postResponse = json_encode($responseArray);
        $parameters = [];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
                       ->disableOriginalConstructor()
                       ->getMock();
        $client->expects($this->once())
               ->method('post')
               ->with(
                   '/issues/1/relations.json',
                   json_encode([
                       'relation' => [
                           'relation_type' => 'relates',
                       ],
                   ])
               )
               ->willReturn($postResponse);

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($responseArray, $api->create(1, $parameters));
    }
}

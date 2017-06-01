<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\IssueCategory;

/**
 * @coversDefaultClass \Redmine\Api\IssueCategory
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssueCategoryTest extends \PHPUnit\Framework\TestCase
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($projectId));
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
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/issue_categories.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($projectId, $parameters));
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
        $getResponse = [
            'issue_categories' => [
                ['id' => 1, 'name' => 'IssueCategory 1'],
                ['id' => 5, 'name' => 'IssueCategory 5'],
            ],
        ];
        $expectedReturn = [
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories')
            )
            ->willReturn($getResponse);

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
        $getResponse = [
            'issue_categories' => [
                ['id' => 1, 'name' => 'IssueCategory 1'],
                ['id' => 5, 'name' => 'IssueCategory 5'],
            ],
        ];
        $expectedReturn = [
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories')
            )
            ->willReturn($getResponse);

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
        $getResponse = [
            'issue_categories' => [
                ['id' => 1, 'name' => 'IssueCategory 1'],
                ['id' => 5, 'name' => 'IssueCategory 5'],
            ],
        ];
        $expectedReturn = [
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories')
            )
            ->willReturn($getResponse);

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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith('/issue_categories/5.json'))
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
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
                    $this->stringStartsWith('/issue_categories/5'),
                    $this->logicalXor(
                        $this->stringContains('.json?'),
                        $this->stringContains('.xml?')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
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
        $getResponse = 'API Response';
        $parameters = ['not-used'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
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
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5, $parameters));
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
        $getResponse = [
            'issue_categories' => [
                ['id' => 5, 'name' => 'IssueCategory 5'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json')
            )
            ->willReturn($getResponse);

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
     * @expectedException \Exception
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
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new IssueCategory($client);

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
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test Category',
            'assigned_to_id' => 2,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects/5/issue_categories.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue_category>'),
                    $this->stringEndsWith('</issue_category>'."\n"),
                    $this->stringContains('<name>Test Category</name>'),
                    $this->stringContains('<assigned_to_id>2</assigned_to_id>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(5, $parameters));
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
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test Category',
            'assigned_to_id' => 2,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issue_categories/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue_category>'),
                    $this->stringEndsWith('</issue_category>'."\n"),
                    $this->stringContains('<name>Test Category</name>'),
                    $this->stringContains('<assigned_to_id>2</assigned_to_id>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }
}

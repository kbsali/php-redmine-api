<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Project;

/**
 * @coversDefaultClass \Redmine\Api\Project
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class ProjectTest extends \PHPUnit\Framework\TestCase
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with('/projects.json')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
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
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($parameters));
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
            ->with(
                '/projects/5.json?include='.
                urlencode('trackers,issue_categories,attachments,relations')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowReturnsClientGetResponseWithUniqueParameters()
    {
        // Test values
        $parameters = ['include' => ['parameter1', 'parameter2', 'enabled_modules']];
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5.json?include='),
                    $this->stringContains(urlencode('parameter1,parameter2,enabled_modules'))
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5, $parameters));
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
            ->with('/projects/5.xml')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
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
            'projects' => [
                ['id' => 1, 'name' => 'Project 1'],
                ['id' => 5, 'name' => 'Project 5'],
            ],
        ];
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
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
            'projects' => [
                ['id' => 1, 'name' => 'Project 1'],
                ['id' => 5, 'name' => 'Project 5'],
            ],
        ];
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing());
        $this->assertSame($expectedReturn, $api->listing());
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
            'projects' => [
                ['id' => 1, 'name' => 'Project 1'],
                ['id' => 5, 'name' => 'Project 5'],
            ],
        ];
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
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
            'projects' => [
                ['id' => 5, 'name' => 'Project 5'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('Project 1'));
        $this->assertSame(5, $api->getIdByName('Project 5'));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionWithEmptyParameters()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create(['id' => 5]));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionIfIdentifierIsMissingInParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'name' => 'Test Project',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @expectedException \Exception
     * @test
     */
    public function testCreateThrowsExceptionIfNameIsMissingInParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'identifier' => 'test-project',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
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
        $getResponse = 'API Response';
        $parameters = [
            'identifier' => 'test-project',
            'name' => 'Test Project',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<project>'),
                    $this->stringEndsWith('</project>'."\n"),
                    $this->stringContains('<identifier>test-project</identifier>'),
                    $this->stringContains('<name>Test Project</name>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @test
     */
    public function testCreateCallsPostWithTrackers()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'identifier' => 'test-project',
            'name' => 'Test Project',
            'tracker_ids' => [10, 5],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/projects.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<project>'),
                    $this->stringEndsWith('</project>'."\n"),
                    $this->stringContains('<tracker_ids type="array">'),
                    $this->stringContains('<tracker>10</tracker>'),
                    $this->stringContains('<tracker>5</tracker>'),
                    $this->stringContains('</tracker_ids>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
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
            'name' => 'Test Project',
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with('/projects/5.xml')
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }
}

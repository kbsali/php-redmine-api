<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Client\Client;
use Redmine\Exception\MissingParameterException;
use Redmine\Tests\Fixtures\MockClient;
use ReflectionMethod;
use SimpleXMLElement;

/**
 * @coversDefaultClass \Redmine\Api\Project
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class ProjectTest extends TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new Project(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Project::all()` is deprecated since v2.4.0, use `Redmine\Api\Project::list()` instead.',
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
            ->with('/projects.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new Project($client);

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
                    $this->stringStartsWith('/projects.json'),
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
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
    }

    /**
     * Test show().
     *
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
            ->with(
                '/projects/5.json?include=' .
                urlencode('trackers,issue_categories,attachments,relations')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show(5));
    }

    /**
     * Test show().
     *
     * @covers ::show
     * @test
     */
    public function testShowReturnsClientGetResponseWithUniqueParameters()
    {
        // Test values
        $parameters = ['include' => ['parameter1', 'parameter2', 'enabled_modules']];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5.json?include='),
                    $this->stringContains(urlencode('parameter1,parameter2,enabled_modules'))
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
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show(5, $parameters));
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
            ->with('/projects/5.xml')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
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
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
        $response = '{"projects":[{"id":1,"name":"Project 1"},{"id":5,"name":"Project 5"}]}';
        $expectedReturn = [
            'Project 1' => 1,
            'Project 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
        $response = '{"projects":[{"id":5,"name":"Project 5"}]}';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/projects.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

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
        $api = new Project($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`, `identifier`');

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
    public function testCreateThrowsExceptionIfIdentifierIsMissingInParameters()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'name' => 'Test Project',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Project($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`, `identifier`');

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
    public function testCreateThrowsExceptionIfNameIsMissingInParameters()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'identifier' => 'test-project',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);

        // Create the object under test
        $api = new Project($client);

        $this->expectException(MissingParameterException::class);
        $this->expectExceptionMessage('Theses parameters are mandatory: `name`, `identifier`');

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
            'identifier' => 'test-project',
            'name' => 'Test Project with some xml entities: & < > " \' ',
            'description' => 'Description with some xml entities: & < > " \' ',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/projects.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<project>'),
                    $this->stringEndsWith('</project>' . "\n"),
                    $this->stringContains('<identifier>test-project</identifier>'),
                    $this->stringContains('<name>Test Project with some xml entities: &amp; &lt; &gt; " \' </name>'),
                    $this->stringContains('<description>Description with some xml entities: &amp; &lt; &gt; " \' </description>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Project($client);

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
    public function testCreateCallsPostWithTrackers()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'identifier' => 'test-project',
            'name' => 'Test Project',
            'tracker_ids' => [10, 5],
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/projects.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<project>'),
                    $this->stringEndsWith('</project>' . "\n"),
                    $this->stringContains('<tracker_ids type="array">'),
                    $this->stringContains('<tracker>10</tracker>'),
                    $this->stringContains('<tracker>5</tracker>'),
                    $this->stringContains('</tracker_ids>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Project($client);

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
            'name' => 'Test Project',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with('/projects/5.xml')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
    }

    /**
     * @covers \Redmine\Api\Project::prepareParamsXml
     */
    public function testDeprecatedPrepareParamsXml()
    {
        $client = $this->createMock(Client::class);

        $api = new Project($client);

        $method = new ReflectionMethod($api, 'prepareParamsXml');
        $method->setAccessible(true);

        $this->assertInstanceOf(SimpleXMLElement::class, $method->invoke($api, ['id' => 1]));
    }
}

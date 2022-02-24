<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\Wiki
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class WikiTest extends TestCase
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
            ->with('/projects/5/wiki/index.json')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all(5));
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
            'offset' => 10,
            'limit' => 2,
        ];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->any())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/projects/5/wiki/index.json'),
                    $this->stringContains('offset=10'),
                    $this->stringContains('limit=2')
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all(5, $parameters));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowWithNumericIdsReturnsClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/projects/5/wiki/test.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->show(5, 'test'));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowWithIdentifierReturnsClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/projects/test/wiki/example.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->show('test', 'example'));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowWithNumericIdsAndVersionReturnsClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/projects/5/wiki/test/22.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->show(5, 'test', 22));
    }

    /**
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowWithIdentifierAndVersionReturnsClientGetResponse()
    {
        // Test values
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/projects/test/wiki/example/22.json'))
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->show('test', 'example', 22));
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
            ->with('/projects/5/wiki/test.xml')
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5, 'test'));
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

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/projects/5/wiki/test.xml',
                '<?xml version="1.0"?>'."\n".'<wiki_page/>'."\n"
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->create(5, 'test'));
    }

    /**
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @test
     */
    public function testCreateWithParametersCallsPost()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'title' => 'Test Wikipage with xml entities: & < > " \' ',
            'comments' => 'Initial Edit with xml entities: & < > " \' ',
            'text' => 'Some page text with xml entities: & < > " \' ',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/projects/5/wiki/test.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<wiki_page>'),
                    $this->stringEndsWith('</wiki_page>'."\n"),
                    $this->stringContains('<title>Test Wikipage with xml entities: &amp; &lt; &gt; " \' </title>'),
                    $this->stringContains('<comments>Initial Edit with xml entities: &amp; &lt; &gt; " \' </comments>'),
                    $this->stringContains('<text>Some page text with xml entities: &amp; &lt; &gt; " \' </text>')
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->create(5, 'test', $parameters));
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

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/projects/5/wiki/test.xml',
                '<?xml version="1.0"?>'."\n".'<wiki_page/>'."\n"
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, 'test'));
    }

    /**
     * Test update().
     *
     * @covers ::put
     * @covers ::update
     * @test
     */
    public function testUpdateWithParametersCallsPut()
    {
        // Test values
        $response = 'API Response';
        $parameters = [
            'title' => 'Test Wikipage',
            'comments' => 'Initial Edit',
            'text' => 'Some page text',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/projects/5/wiki/test.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<wiki_page>'),
                    $this->stringEndsWith('</wiki_page>'."\n"),
                    $this->stringContains('<title>Test Wikipage</title>'),
                    $this->stringContains('<comments>Initial Edit</comments>'),
                    $this->stringContains('<text>Some page text</text>')
                )
            )
            ->willReturn(true);
        $client->expects($this->once())
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, 'test', $parameters));
    }
}

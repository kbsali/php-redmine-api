<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Client\Client;
use Redmine\Tests\Fixtures\MockClient;

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
     */
    public function testAllTriggersDeprecationWarning()
    {
        $api = new Wiki(MockClient::create());

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Wiki::all()` is deprecated since v2.4.0, use `Redmine\Api\Wiki::listByProject()` instead.',
                    $errstr
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED
        );

        $api->all(5);
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
            ->with('/projects/5/wiki/index.json')
            ->willReturn(true);
        $client->expects($this->atLeast(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn($responseType);

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->all(5));
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
                '<?xml version="1.0"?>' . "\n" . '<wiki_page/>' . "\n"
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
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<wiki_page>'),
                    $this->stringEndsWith('</wiki_page>' . "\n"),
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
                '<?xml version="1.0"?>' . "\n" . '<wiki_page/>' . "\n"
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
                    $this->stringStartsWith('<?xml version="1.0"?>' . "\n" . '<wiki_page>'),
                    $this->stringEndsWith('</wiki_page>' . "\n"),
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

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\AbstractApi;

use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use ReflectionMethod;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\AbstractApi::get
 */
class GetTest extends TestCase
{
    /**
     * @dataProvider getJsonDecodingFromGetMethodData
     */
    public function testJsonDecodingFromGetMethod($response, $decode, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/json');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        if (is_bool($decode)) {
            $this->assertSame($expected, $method->invoke($api, 'path', $decode));
        } else {
            $this->assertSame($expected, $method->invoke($api, 'path'));
        }
    }

    public static function getJsonDecodingFromGetMethodData(): array
    {
        return [
            'test decode by default' => ['{"foo_bar": 12345}', null, ['foo_bar' => 12345]],
            'test decode by default, JSON decode: false' => ['{"foo_bar": 12345}', false, '{"foo_bar": 12345}'],
            'test decode by default, JSON decode: true' => ['{"foo_bar": 12345}', true, ['foo_bar' => 12345]],
            'Empty body, JSON decode: false' => ['', false, false],
            'Empty body, JSON decode: true' => ['', true, false],
            'test invalid JSON' => ['{"foo_bar":', true, 'Error decoding body as JSON: Syntax error'],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi
     * @test
     * @dataProvider getXmlDecodingFromGetMethodData
     */
    public function testXmlDecodingFromRequestMethods($response, $decode, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        $return = $method->invoke($api, 'path', $decode);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
    }

    public static function getXmlDecodingFromGetMethodData(): array
    {
        return [
            ['<?xml version="1.0"?><issue/>', null, '<?xml version="1.0"?><issue/>'], // test decode by default
            ['<?xml version="1.0"?><issue/>', true, '<?xml version="1.0"?><issue/>'],
            ['<?xml version="1.0"?><issue/>', false, '<?xml version="1.0"?><issue/>'], // test that xml decoding will be always happen
        ];
    }
}

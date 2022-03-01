<?php

namespace Redmine\Tests\Unit\Api;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use ReflectionMethod;
use SimpleXMLElement;

/**
 * @coversDefaultClass \Redmine\Api\AbstractApi
 */
class AbstractApiTest extends TestCase
{
    /**
     * @test
     * @dataProvider getIsNotNullReturnsCorrectBooleanData
     */
    public function testIsNotNullReturnsCorrectBoolean(bool $expected, $value)
    {
        $client = $this->createMock(Client::class);

        $api = $this->getMockForAbstractClass(AbstractApi::class, [$client]);

        $method = new ReflectionMethod($api, 'isNotNull');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($api, $value));
    }

    public function getIsNotNullReturnsCorrectBooleanData()
    {
        return [
            [false, null],
            [false, false],
            [false, ''],
            [false, []],
            [true, true],
            [true, 0],
            [true, 1],
            [true, 0.0],
            [true, -0.0],
            [true, 0.5],
            [true, '0'],
            [true, 'string'],
            [true, [0]],
            [true, ['0']],
            [true, ['']],
            [true, new \stdClass()],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi
     * @test
     * @dataProvider getLastCallFailedData
     */
    public function testLastCallFailedReturnsCorrectBoolean($statusCode, $expectedBoolean)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseStatusCode')->willReturn($statusCode);

        $api = $this->getMockForAbstractClass(AbstractApi::class, [$client]);

        $this->assertSame($expectedBoolean, $api->lastCallFailed());
    }

    public function getLastCallFailedData()
    {
        return [
            [100, true],
            [101, true],
            [102, true],
            [103, true],
            [103, true],
            [200, false],
            [201, false],
            [202, true],
            [203, true],
            [204, true],
            [205, true],
            [206, true],
            [207, true],
            [208, true],
            [226, true],
            [300, true],
            [301, true],
            [302, true],
            [303, true],
            [304, true],
            [305, true],
            [306, true],
            [307, true],
            [308, true],
            [400, true],
            [401, true],
            [402, true],
            [403, true],
            [404, true],
            [405, true],
            [406, true],
            [407, true],
            [408, true],
            [409, true],
            [410, true],
            [411, true],
            [412, true],
            [413, true],
            [414, true],
            [415, true],
            [416, true],
            [417, true],
            [421, true],
            [422, true],
            [423, true],
            [424, true],
            [425, true],
            [426, true],
            [428, true],
            [429, true],
            [431, true],
            [451, true],
            [500, true],
            [501, true],
            [502, true],
            [503, true],
            [504, true],
            [505, true],
            [506, true],
            [507, true],
            [508, true],
            [509, true],
            [510, true],
            [511, true],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi
     * @test
     * @dataProvider getJsonDecodingFromGetMethodData
     */
    public function testJsonDecodingFromGetMethod($response, $decode, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/json');

        $api = $this->getMockForAbstractClass(AbstractApi::class, [$client]);

        $method = new ReflectionMethod($api, 'get');
        $method->setAccessible(true);

        // Perform the tests
        if (is_bool($decode)) {
            $this->assertSame($expected, $method->invoke($api, 'path', $decode));
        } else {
            $this->assertSame($expected, $method->invoke($api, 'path'));
        }
    }

    public function getJsonDecodingFromGetMethodData()
    {
        return [
            ['{"foo_bar": 12345}', null, ['foo_bar' => 12345]], // test decode by default
            ['{"foo_bar": 12345}', false, '{"foo_bar": 12345}'],
            ['{"foo_bar": 12345}', true, ['foo_bar' => 12345]],
            'Empty body, JSON decode: false' => ['', false, false],
            'Empty body, JSON decode: true' => ['', true, false],
            ['{"foo_bar":', true, 'Error decoding body as JSON: Syntax error'], // test invalid JSON
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi
     * @test
     * @dataProvider getXmlDecodingFromGetMethodData
     */
    public function testXmlDecodingFromRequestMethods($methodName, $response, $decode, $expected)
    {
        $xmlToString = function (SimpleXMLElement $xmlElement) {
            $dom = new DOMDocument('1.0');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = false;
            $dom->loadXML($xmlElement->asXML());

            // Remove line breaks
            return preg_replace("/\r|\n/", '', $dom->saveXML());
        };

        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = $this->getMockForAbstractClass(AbstractApi::class, [$client]);

        $method = new ReflectionMethod($api, $methodName);
        $method->setAccessible(true);

        // Perform the tests
        if ('get' === $methodName) {
            $return = $method->invoke($api, 'path', $decode);

            $this->assertInstanceOf(SimpleXMLElement::class, $return);
            $this->assertSame($expected, $xmlToString($return));
        } elseif ('delete' === $methodName) {
            $return = $method->invoke($api, 'path');

            $this->assertSame($expected, $return);
        } else {
            $return = $method->invoke($api, 'path', '');

            $this->assertInstanceOf(SimpleXMLElement::class, $return);
            $this->assertSame($expected, $xmlToString($return));
        }
    }

    public function getXmlDecodingFromGetMethodData()
    {
        return [
            ['get', '<?xml version="1.0"?><issue/>', null, '<?xml version="1.0"?><issue/>'], // test decode by default
            ['get', '<?xml version="1.0"?><issue/>', true, '<?xml version="1.0"?><issue/>'],
            ['get', '<?xml version="1.0"?><issue/>', false, '<?xml version="1.0"?><issue/>'], // test that xml decoding will be always happen
            ['post', '<?xml version="1.0"?><issue/>', null, '<?xml version="1.0"?><issue/>'],
            ['put', '<?xml version="1.0"?><issue/>', null, '<?xml version="1.0"?><issue/>'],
            ['delete', '<?xml version="1.0"?><issue/>', null, '<?xml version="1.0"?><issue/>'],
        ];
    }
}

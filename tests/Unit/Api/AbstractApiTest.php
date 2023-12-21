<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use Redmine\Exception\SerializerException;
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

        $api = new class($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'isNotNull');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($api, $value));
    }

    public static function getIsNotNullReturnsCorrectBooleanData(): array
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

        $api = new class($client) extends AbstractApi {};

        $this->assertSame($expectedBoolean, $api->lastCallFailed());
    }

    public static function getLastCallFailedData(): array
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

        $api = new class($client) extends AbstractApi {};

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
    public function testXmlDecodingFromRequestMethods($methodName, $response, $decode, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, $methodName);
        $method->setAccessible(true);

        // Perform the tests
        if ('get' === $methodName) {
            $return = $method->invoke($api, 'path', $decode);

            $this->assertInstanceOf(SimpleXMLElement::class, $return);
            $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
        } elseif ('delete' === $methodName) {
            $return = $method->invoke($api, 'path');

            $this->assertSame($expected, $return);
        } else {
            $return = $method->invoke($api, 'path', '');

            $this->assertInstanceOf(SimpleXMLElement::class, $return);
            $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
        }
    }

    public static function getXmlDecodingFromGetMethodData(): array
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

    /**
     * @covers \Redmine\Api\AbstractApi::retrieveData
     *
     * @dataProvider retrieveDataData
     */
    public function testRetrieveData($response, $contentType, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('requestGet')->willReturn(true);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = new class($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($api, '/issues.json'));
    }

    public static function retrieveDataData(): array
    {
        return [
            'test decode by default' => ['{"foo_bar": 12345}', 'application/json', ['foo_bar' => 12345]],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi::retrieveData
     *
     * @dataProvider getRetrieveDataToExceptionData
     */
    public function testRetrieveDataThrowsException($response, $contentType, $expectedException, $expectedMessage)
    {
        $client = $this->createMock(Client::class);
        $client->method('requestGet')->willReturn(true);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = new class($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');
        $method->setAccessible(true);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $method->invoke($api, '/issues.json');
    }

    public static function getRetrieveDataToExceptionData(): array
    {
        return [
            'Empty body' => ['', 'application/json', SerializerException::class, 'Syntax error" while decoding JSON: '],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi::retrieveAll
     *
     * @dataProvider getRetrieveAllData
     */
    public function testDeprecatedRetrieveAll($content, $contentType, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('requestGet')->willReturn(true);
        $client->method('getLastResponseBody')->willReturn($content);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = new class($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveAll');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($api, ''));
    }

    public static function getRetrieveAllData(): array
    {
        return [
            'test decode by default' => ['{"foo_bar": 12345}', 'application/json', ['foo_bar' => 12345]],
            'String' => ['"string"', 'application/json', 'Could not convert response body into array: "string"'],
            'Empty body' => ['', 'application/json', 'Catched error "Syntax error" while decoding JSON: '],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi::attachCustomFieldXML
     */
    public function testDeprecatedAttachCustomFieldXML()
    {
        $client = $this->createMock(Client::class);

        $api = new class($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'attachCustomFieldXML');
        $method->setAccessible(true);

        $xml = new SimpleXMLElement('<?xml version="1.0"?><issue/>');

        $this->assertInstanceOf(SimpleXMLElement::class, $method->invoke($api, $xml, []));
    }
}

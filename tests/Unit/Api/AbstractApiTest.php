<?php

namespace Redmine\Tests\Unit\Api;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use Redmine\Exception\SerializerException;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;
use ReflectionMethod;
use SimpleXMLElement;

/**
 * @coversDefaultClass \Redmine\Api\AbstractApi
 */
class AbstractApiTest extends TestCase
{
    public function testCreateWithHttpClientWorks()
    {
        $client = $this->createMock(HttpClient::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'getHttpClient');
        $method->setAccessible(true);

        $this->assertSame($client, $method->invoke($api));
    }

    public function testCreateWitClientWorks()
    {
        $client = $this->createMock(Client::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'getHttpClient');
        $method->setAccessible(true);

        $this->assertInstanceOf(HttpClient::class, $method->invoke($api));
    }

    public function testCreateWithoutClitentOrHttpClientThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\AbstractApi::__construct(): Argument #1 ($client) must be of type Redmine\Client\Client or Redmine\Http\HttpClient, `stdClass` given');

        new class (new \stdClass()) extends AbstractApi {};
    }

    /**
     * @test
     * @dataProvider getIsNotNullReturnsCorrectBooleanData
     */
    public function testIsNotNullReturnsCorrectBoolean(bool $expected, $value)
    {
        $client = $this->createMock(Client::class);

        $api = new class ($client) extends AbstractApi {};

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
     * @covers \Redmine\Api\AbstractApi::lastCallFailed
     */
    public function testLastCallFailedPreventsRaceCondition()
    {
        $response1 = $this->createMock(Response::class);
        $response1->method('getStatusCode')->willReturn(200);

        $response2 = $this->createMock(Response::class);
        $response2->method('getStatusCode')->willReturn(500);

        $client = $this->createMock(HttpClient::class);
        $client->method('request')->willReturnMap([
            ['GET', '200.json', $response1],
            ['GET', '500.json', $response2],
        ]);

        $api1 = new class ($client) extends AbstractApi {
            public function __construct($client)
            {
                parent::__construct($client);
                parent::get('200.json', false);
            }
        };

        $api2 = new class ($client) extends AbstractApi {
            public function __construct($client)
            {
                parent::__construct($client);
                parent::get('500.json', false);
            }
        };

        $this->assertSame(false, $api1->lastCallFailed());
        $this->assertSame(true, $api2->lastCallFailed());
    }

    /**
     * @covers \Redmine\Api\AbstractApi::lastCallFailed
     * @test
     * @dataProvider getLastCallFailedData
     */
    public function testLastCallFailedWithClientReturnsCorrectBoolean($statusCode, $expectedBoolean)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseStatusCode')->willReturn($statusCode);

        $api = new class ($client) extends AbstractApi {};

        $this->assertSame($expectedBoolean, $api->lastCallFailed());
    }

    /**
     * @covers \Redmine\Api\AbstractApi::lastCallFailed
     * @test
     * @dataProvider getLastCallFailedData
     */
    public function testLastCallFailedWithHttpClientReturnsCorrectBoolean($statusCode, $expectedBoolean)
    {
        $response = $this->createMock(Response::class);
        $response->method('getStatusCode')->willReturn($statusCode);

        $client = $this->createMock(HttpClient::class);
        $client->method('request')->willReturn($response);

        $api = new class ($client) extends AbstractApi {
            public function __construct($client)
            {
                parent::__construct($client);
                $this->get('', false);
            }
        };

        $this->assertSame($expectedBoolean, $api->lastCallFailed());
    }

    public static function getLastCallFailedData(): array
    {
        return [
            [0, true],
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
     * @dataProvider getXmlDecodingFromRequestMethodsData
     */
    public function testXmlDecodingFromRequestMethods($methodName, $response, $decode, $expected)
    {
        $client = $this->createMock(Client::class);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn('application/xml');

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, $methodName);
        $method->setAccessible(true);

        // Perform the tests
        if ('delete' === $methodName) {
            $return = $method->invoke($api, 'path');

            $this->assertSame($expected, $return);
        } else {
            $return = $method->invoke($api, 'path', '');

            $this->assertInstanceOf(SimpleXMLElement::class, $return);
            $this->assertXmlStringEqualsXmlString($expected, $return->asXML());
        }
    }

    public static function getXmlDecodingFromRequestMethodsData(): array
    {
        return [
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

        $api = new class ($client) extends AbstractApi {};

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

        $api = new class ($client) extends AbstractApi {};

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

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveAll');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($api, ''));
    }

    public static function getRetrieveAllData(): array
    {
        return [
            'array response' => ['{"foo_bar": 12345}', 'application/json', ['foo_bar' => 12345]],
            'string response' => ['"string"', 'application/json', 'Could not convert response body into array: "string"'],
            'false response' => ['', 'application/json', false],
        ];
    }

    /**
     * @covers \Redmine\Api\AbstractApi::attachCustomFieldXML
     */
    public function testDeprecatedAttachCustomFieldXML()
    {
        $client = $this->createMock(Client::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'attachCustomFieldXML');
        $method->setAccessible(true);

        $xml = new SimpleXMLElement('<?xml version="1.0"?><issue/>');

        $this->assertInstanceOf(SimpleXMLElement::class, $method->invoke($api, $xml, []));
    }
}

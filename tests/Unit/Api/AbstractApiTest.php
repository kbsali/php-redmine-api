<?php

namespace Redmine\Tests\Unit\Api;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use Redmine\Exception\SerializerException;
use Redmine\Http\HttpClient;
use Redmine\Http\Response;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use ReflectionMethod;
use SimpleXMLElement;

#[CoversClass(AbstractApi::class)]
class AbstractApiTest extends TestCase
{
    public function testCreateWithHttpClientWorks(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'getHttpClient');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertSame($client, $method->invoke($api));
    }

    public function testCreateWitClientWorks(): void
    {
        $client = $this->createStub(Client::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'getHttpClient');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertInstanceOf(HttpClient::class, $method->invoke($api));
    }

    public function testCreateWithoutClitentOrHttpClientThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\AbstractApi::__construct(): Argument #1 ($client) must be of type Redmine\Client\Client or Redmine\Http\HttpClient, `stdClass` given');

        /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
        new class (new \stdClass()) extends AbstractApi {};
    }

    public function testGetTriggersDeprecationWarning(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {
            public function runGet($path)
            {
                return $this->get($path);
            }
        };

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\AbstractApi::get()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->runGet('/path.json');
    }

    public function testGetLastResponseWithHttpClientWorks(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {};

        $this->assertInstanceOf(Response::class, $api->getLastResponse());
    }

    public function testPostTriggersDeprecationWarning(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {
            public function runPost($path, $data)
            {
                return $this->post($path, $data);
            }
        };

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\AbstractApi::post()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->runPost('/path.json', 'data');
    }

    public function testPutTriggersDeprecationWarning(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {
            public function runPut($path, $data)
            {
                return $this->put($path, $data);
            }
        };

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\AbstractApi::put()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->runPut('/path.json', 'data');
    }

    public function testDeleteTriggersDeprecationWarning(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {
            public function runDelete($path)
            {
                return $this->delete($path);
            }
        };

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\AbstractApi::delete()` is deprecated since v2.6.0, use `\Redmine\Http\HttpClient::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->runDelete('/path.json');
    }

    /**
     * @dataProvider getIsNotNullReturnsCorrectBooleanData
     *
     * @param mixed $value
     */
    #[DataProvider('getIsNotNullReturnsCorrectBooleanData')]
    public function testIsNotNullReturnsCorrectBoolean(bool $expected, $value): void
    {
        $client = $this->createStub(Client::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'isNotNull');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

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

    public function testLastCallFailedPreventsRaceCondition(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '200.json',
                'application/json',
                '',
                200,
            ],
            [
                'GET',
                '500.json',
                'application/json',
                '',
                500,
            ],
        );

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

        $api3 = new class ($client) extends AbstractApi {};

        $this->assertSame(false, $api1->lastCallFailed());
        $this->assertSame(true, $api2->lastCallFailed());
        $this->assertSame(true, $api3->lastCallFailed());
    }

    /**
     * @dataProvider getLastCallFailedData
     */
    #[DataProvider('getLastCallFailedData')]
    public function testLastCallFailedWithClientReturnsCorrectBoolean(int $statusCode, bool $expectedBoolean): void
    {
        $client = $this->createStub(Client::class);
        $client->method('getLastResponseStatusCode')->willReturn($statusCode);

        $api = new class ($client) extends AbstractApi {};

        $this->assertSame($expectedBoolean, $api->lastCallFailed());
    }

    /**
     * @dataProvider getLastCallFailedData
     */
    #[DataProvider('getLastCallFailedData')]
    public function testLastCallFailedWithHttpClientReturnsCorrectBoolean(int $statusCode, bool $expectedBoolean): void
    {
        $response = $this->createStub(Response::class);
        $response->method('getStatusCode')->willReturn($statusCode);

        $client = $this->createStub(HttpClient::class);
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
     * @dataProvider retrieveDataData
     */
    #[DataProvider('retrieveDataData')]
    public function testRetrieveData(string $path, string $contentType, string $response, array $expected): void
    {
        $client = $this->createStub(Client::class);
        $client->method('requestGet')->willReturn(true);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $this->assertSame($expected, $method->invoke($api, $path));
    }

    public static function retrieveDataData(): array
    {
        return [
            'test json decode by default' => [
                '/issues.json',
                'application/json',
                '{"foo_bar": 12345}',
                ['foo_bar' => 12345],
            ],
            'test xml decode by default' => [
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issues type="array"></issues>',
                ['@attributes' => ['type' => 'array']],
            ],
        ];
    }

    public function testRetrieveDataWith100ResultsMakes1Request(): void
    {
        $response1 = $this->createStub(Response::class);
        $response1->method('getContentType')->willReturn('application/json');
        $response1->method('getContent')->willReturn('{"total_count":100,"offset":0,"limit":100,"data":[]}');

        $client = $this->createMock(HttpClient::class);
        $client->expects($this->once())->method('request')->willReturn($response1);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $method->invoke($api, '/data.json', ['limit' => 101]);
    }

    public function testRetrieveDataWith250ResultsMakes3Requests(): void
    {
        $response1 = $this->createStub(Response::class);
        $response1->method('getContentType')->willReturn('application/json');
        $response1->method('getContent')->willReturn('{"total_count":250,"offset":0,"limit":100,"data":[]}');

        $response2 = $this->createStub(Response::class);
        $response2->method('getContentType')->willReturn('application/json');
        $response2->method('getContent')->willReturn('{"total_count":250,"offset":100,"limit":100,"data":[]}');

        $response3 = $this->createStub(Response::class);
        $response3->method('getContentType')->willReturn('application/json');
        $response3->method('getContent')->willReturn('{"total_count":250,"offset":200,"limit":100,"data":[]}');

        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(3))->method('request')->willReturnOnConsecutiveCalls($response1, $response2, $response3);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $method->invoke($api, '/data.json', ['limit' => 301]);
    }

    /**
     * @dataProvider getRetrieveDataToExceptionData
     */
    #[DataProvider('getRetrieveDataToExceptionData')]
    public function testRetrieveDataThrowsException(string $response, string $contentType, string $expectedException, string $expectedMessage): void
    {
        $client = $this->createStub(Client::class);
        $client->method('requestGet')->willReturn(true);
        $client->method('getLastResponseBody')->willReturn($response);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

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
     * @dataProvider getRetrieveAllData
     *
     * @param mixed $expected
     */
    #[DataProvider('getRetrieveAllData')]
    public function testDeprecatedRetrieveAll(string $content, string $contentType, $expected): void
    {
        $client = $this->createStub(Client::class);
        $client->method('requestGet')->willReturn(true);
        $client->method('getLastResponseBody')->willReturn($content);
        $client->method('getLastResponseContentType')->willReturn($contentType);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveAll');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

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

    public function testDeprecatedAttachCustomFieldXML(): void
    {
        $client = $this->createStub(Client::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'attachCustomFieldXML');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        $xml = new SimpleXMLElement('<?xml version="1.0"?><issue/>');

        $this->assertInstanceOf(SimpleXMLElement::class, $method->invoke($api, $xml, []));
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\Client;
use Redmine\Exception\SerializerException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Http\Response;
use ReflectionMethod;

#[CoversClass(AbstractApi::class)]
class AbstractApiTest extends TestCase
{
    public function testCreateWithHttpClientWorks(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'getHttpClient');

        $this->assertSame($client, $method->invoke($api));
    }

    public function testCreateWithClientAndHttpClientWorks(): void
    {
        /** @var Client&HttpClient&\PHPUnit\Framework\MockObject\Stub $client */
        $client = $this->createStubForIntersectionOfInterfaces([Client::class, HttpClient::class]);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'getHttpClient');

        $this->assertSame($client, $method->invoke($api));
    }

    public function testCreateWithClientWithoutHttpClientThrowsException(): void
    {
        $client = $this->createStub(Client::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\AbstractApi::__construct(): Argument #1 ($client) must be of type Redmine\Http\HttpClient, `');

        /** @phpstan-ignore-next-line We are providing a Client that is not a HttpClient to test the exception */
        new class ($client) extends AbstractApi {};
    }

    public function testCreateWithoutClitentOrHttpClientThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\AbstractApi::__construct(): Argument #1 ($client) must be of type Redmine\Http\HttpClient, `stdClass` given');

        /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
        new class (new \stdClass()) extends AbstractApi {};
    }

    public function testCreateWithNonObjectThrowsExceptionWithDebugType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Redmine\Api\AbstractApi::__construct(): Argument #1 ($client) must be of type Redmine\Http\HttpClient, `int` given');

        /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
        new class (123) extends AbstractApi {};
    }

    public function testGetLastResponseWithHttpClientWorks(): void
    {
        $client = $this->createStub(HttpClient::class);

        $api = new class ($client) extends AbstractApi {};

        $this->assertInstanceOf(Response::class, $api->getLastResponse());
    }

    /**
     * @dataProvider retrieveDataData
     *
     * @param array<mixed> $expected
     */
    #[DataProvider('retrieveDataData')]
    public function testRetrieveData(string $path, string $contentType, string $response, array $expected): void
    {
        $client = $this->createStub(HttpClient::class);
        $client->method('request')->willReturn(HttpFactory::makeResponse(200, $contentType, $response));

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');

        $this->assertSame($expected, $method->invoke($api, $path));
    }

    /**
     * @return array<array<mixed>>
     */
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
        $response1->method('getStatusCode')->willReturn(200);
        $response1->method('getContentType')->willReturn('application/json');
        $response1->method('getContent')->willReturn('{"total_count":100,"offset":0,"limit":100,"data":[]}');

        $client = $this->createMock(HttpClient::class);
        $client->expects($this->once())->method('request')->willReturn($response1);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');

        $method->invoke($api, '/data.json', ['limit' => 101]);
    }

    public function testRetrieveDataWith250ResultsMakes3Requests(): void
    {
        $response1 = $this->createStub(Response::class);
        $response1->method('getStatusCode')->willReturn(200);
        $response1->method('getContentType')->willReturn('application/json');
        $response1->method('getContent')->willReturn('{"total_count":250,"offset":0,"limit":100,"data":[]}');

        $response2 = $this->createStub(Response::class);
        $response2->method('getStatusCode')->willReturn(200);
        $response2->method('getContentType')->willReturn('application/json');
        $response2->method('getContent')->willReturn('{"total_count":250,"offset":100,"limit":100,"data":[]}');

        $response3 = $this->createStub(Response::class);
        $response3->method('getStatusCode')->willReturn(200);
        $response3->method('getContentType')->willReturn('application/json');
        $response3->method('getContent')->willReturn('{"total_count":250,"offset":200,"limit":100,"data":[]}');

        $client = $this->createMock(HttpClient::class);
        $client->expects($this->exactly(3))->method('request')->willReturnOnConsecutiveCalls($response1, $response2, $response3);

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');

        $method->invoke($api, '/data.json', ['limit' => 301]);
    }

    /**
     * @dataProvider getRetrieveDataToExceptionData
     */
    #[DataProvider('getRetrieveDataToExceptionData')]
    public function testRetrieveDataThrowsException(string $response, string $contentType, string $expectedException, string $expectedMessage): void
    {
        $client = $this->createStub(HttpClient::class);
        $client->method('request')->willReturn(HttpFactory::makeResponse(200, $contentType, $response));

        $api = new class ($client) extends AbstractApi {};

        $method = new ReflectionMethod($api, 'retrieveData');

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedMessage);

        $method->invoke($api, '/issues.json');
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getRetrieveDataToExceptionData(): array
    {
        return [
            'Empty body' => ['', 'application/json', SerializerException::class, 'Syntax error" while decoding JSON: '],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\Psr18ClientTest;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Redmine\Client\Psr18Client;
use Redmine\Exception\ClientException;
use Redmine\Http\Request;
use Redmine\Http\Response;

#[CoversClass(Psr18Client::class)]
class RequestTest extends TestCase
{
    /**
     * @dataProvider getRequestReponseData
     */
    #[DataProvider('getRequestReponseData')]
    public function testRequestReturnsCorrectResponse(string $method, string $data, int $statusCode, string $contentType, string $content): void
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('__toString')->willReturn($content);

        $response = $this->createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getHeaderLine')->willReturn($contentType);
        $response->method('getBody')->willReturn($stream);

        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $request = $this->createStub(RequestInterface::class);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $request = $this->createStub(Request::class);
        $request->method('getMethod')->willReturn($method);
        $request->method('getPath')->willReturn('/path');
        $request->method('getContentType')->willReturn($contentType);
        $request->method('getContent')->willReturn($data);

        $response = $client->request($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($contentType, $response->getContentType());
        $this->assertSame($content, $response->getContent());
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getRequestReponseData(): array
    {
        return [
            ['GET', '', 101, '', ''],
            ['GET', '', 101, 'text/plain', ''],
            ['GET', '', 200, 'application/json', '{"foo_bar": 12345}'],
            ['GET', '', 301, 'application/xml', ''],
            ['GET', '', 404, 'application/json', '{"title": "404 Not Found"}'],
            ['GET', '', 500, 'text/plain', 'Internal Server Error'],
            ['POST', '{"foo":"bar"}', 101, 'text/plain', ''],
            ['POST', '{"foo":"bar"}', 200, 'application/json', '{"foo_bar": 12345}'],
            ['POST', '{"foo":"bar"}', 301, 'application/json', ''],
            ['POST', '{"foo":"bar"}', 404, 'application/json', '{"title": "404 Not Found"}'],
            ['POST', '{"foo":"bar"}', 500, 'text/plain', 'Internal Server Error'],
            ['PUT', '{"foo":"bar"}', 101, 'text/plain', ''],
            ['PUT', '{"foo":"bar"}', 200, 'application/json', '{"foo_bar": 12345}'],
            ['PUT', '{"foo":"bar"}', 301, 'application/json', ''],
            ['PUT', '{"foo":"bar"}', 404, 'application/json', '{"title": "404 Not Found"}'],
            ['PUT', '{"foo":"bar"}', 500, 'text/plain', 'Internal Server Error'],
            ['DELETE', '', 101, 'text/plain', ''],
            ['DELETE', '', 200, 'application/json', '{"foo_bar": 12345}'],
            ['DELETE', '', 301, 'application/json', ''],
            ['DELETE', '', 404, 'application/json', '{"title": "404 Not Found"}'],
            ['DELETE', '', 500, 'text/plain', 'Internal Server Error'],
        ];
    }

    public function testRequestThrowsClientException(): void
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->exactly(1))->method('sendRequest')->willThrowException(
            new class ('error message') extends Exception implements ClientExceptionInterface {},
        );

        $request = $this->createStub(RequestInterface::class);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $request = $this->createStub(Request::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getPath')->willReturn('/path');
        $request->method('getContentType')->willReturn('application/json');
        $request->method('getContent')->willReturn('');

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('error message');

        $client->request($request);
    }
}

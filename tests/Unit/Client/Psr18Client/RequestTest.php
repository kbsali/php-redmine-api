<?php

namespace Redmine\Tests\Unit\Client\Psr18ClientTest;

use Exception;
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

/**
 * @covers \Redmine\Client\Psr18Client::request
 * @covers \Redmine\Client\Psr18Client::runRequest
 * @covers \Redmine\Client\Psr18Client::createRequest
 */
class RequestTest extends TestCase
{
    /**
     * @dataProvider getRequestReponseData
     */
    public function testRequestReturnsCorrectResponse($method, $data, $statusCode, $contentType, $content)
    {
        $httpClient = $this->createConfiguredMock(ClientInterface::class, [
            'sendRequest' => $this->createConfiguredMock(ResponseInterface::class, [
                'getStatusCode' => $statusCode,
                'getHeaderLine' => $contentType,
                'getBody' => $this->createConfiguredMock(StreamInterface::class, [
                    '__toString' => $content,
                ]),
            ])
        ]);

        $requestFactory = $this->createConfiguredMock(RequestFactoryInterface::class, [
            'createRequest' => (function() {
                $request = $this->createMock(RequestInterface::class);
                $request->method('withHeader')->willReturn($request);
                $request->method('withBody')->willReturn($request);

                return $request;
            })(),
        ]);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $request = $this->createConfiguredMock(Request::class, [
            'getMethod' => $method,
            'getPath' => '/path',
            'getContentType' => $contentType,
            'getContent' => $data,
        ]);

        $response = $client->request($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame($contentType, $response->getContentType());
        $this->assertSame($content, $response->getContent());
    }

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

    public function testRequestThrowsClientException()
    {
        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->expects($this->exactly(1))->method('sendRequest')->willThrowException(
            new class('error message') extends Exception implements ClientExceptionInterface {}
        );

        $requestFactory = $this->createConfiguredMock(RequestFactoryInterface::class, [
            'createRequest' => (function() {
                $request = $this->createMock(RequestInterface::class);
                $request->method('withHeader')->willReturn($request);
                $request->method('withBody')->willReturn($request);

                return $request;
            })(),
        ]);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $request = $this->createConfiguredMock(Request::class, [
            'getMethod' => 'GET',
            'getPath' => '/path',
            'getContentType' => 'application/json',
            'getContent' => '',
        ]);

        $this->expectException(ClientException::class);
        $this->expectExceptionMessage('error message');

        $client->request($request);
    }
}

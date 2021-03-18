<?php

declare(strict_types=1);

namespace Redmine\Tests\Integration;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Redmine\Psr18Client;

class Psr18ClientRequestGenerationTest extends TestCase
{
    /**
     * @covers \Redmine\Psr18Client
     * @test
     *
     * @dataProvider createdGetRequestsData
     */
    public function testCreateRequestsContainsRelevantData($path, $expectedOutput)
    {
        $response = $this->createMock(ResponseInterface::class);

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->method('sendRequest')->will(
            $this->returnCallback(function($request) use ($response, $expectedOutput) {
                $content = $request->getBody()->__toString();

                $cookieHeader = '';
                $cookies = [];

                foreach ($request->getCookieParams() as $k => $v) {
                    $cookies[] = $k.'='.$v;
                }

                $headers = '';

                foreach ($request->getHeaders() as $k => $v) {
                    $headers .= $k . ": " . $request->getHeaderLine($k)."\r\n";
                }

                if (!empty($cookies)) {
                    $cookieHeader = 'Cookie: '.implode('; ', $cookies)."\r\n";
                }

                $fullRequest = sprintf(
                    '%s %s %s',
                    $request->getMethod(),
                    $request->getUri()->__toString(),
                    $request->getProtocolVersion()
                )."\r\n".
                    $headers.
                    $cookieHeader."\r\n".
                    $content
                ;

                $this->assertSame($expectedOutput, $fullRequest);

                return $response;
            })
        );

        $requestFactory = $this->createMock(ServerRequestFactoryInterface::class);
        $requestFactory->method('createServerRequest')->will(
            $this->returnCallback(function($method, $uri) {
                return new ServerRequest($method, $uri);
            })
        );

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $client->requestGet($path);
    }

    public function createdGetRequestsData()
    {
        return [
            ['/path', "GET http://test.local/path 1.1\r\nHost: test.local\r\n\r\n"],
            ['/path.json', "GET http://test.local/path.json 1.1\r\nHost: test.local\r\n\r\n"],
        ];
    }
}

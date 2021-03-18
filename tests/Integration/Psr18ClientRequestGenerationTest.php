<?php

declare(strict_types=1);

namespace Redmine\Tests\Integration;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Redmine\Client\Psr18Client;

class Psr18ClientRequestGenerationTest extends TestCase
{
    /**
     * @covers \Redmine\Psr18Client
     * @test
     *
     * @dataProvider createdGetRequestsData
     */
    public function testPsr18ClientCreatesCorrectRequests(
        string $url, string $apikeyOrUsername, $pwd,
        string $method, string $path, $data,
        $expectedOutput
    ) {
        $response = $this->createMock(ResponseInterface::class);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->will(
            $this->returnCallback(function($request) use ($response, $expectedOutput) {
                // Create a text representation of the HTTP request
                $content = $request->getBody()->__toString();

                $headers = '';

                foreach ($request->getHeaders() as $k => $v) {
                    $headers .= $k . ": " . $request->getHeaderLine($k).\PHP_EOL;
                }

                $cookies = [];

                foreach ($request->getCookieParams() as $k => $v) {
                    $cookies[] = $k.'='.$v;
                }

                if (!empty($cookies)) {
                    $headers .= 'Cookie: '.implode('; ', $cookies).\PHP_EOL;
                }

                $fullRequest = sprintf(
                        '%s %s %s',
                        $request->getMethod(),
                        $request->getUri()->__toString(),
                        $request->getProtocolVersion()
                    ).\PHP_EOL.
                    $headers.\PHP_EOL.
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

        $streamFactory = new class() implements StreamFactoryInterface {
            public function createStream(string $content = ''): StreamInterface
            {
                return Utils::streamFor($content);
            }

            public function createStreamFromFile(string $file, string $mode = 'r'): StreamInterface
            {
                return Utils::streamFor(Utils::tryFopen($file, $mode));
            }

            public function createStreamFromResource($resource): StreamInterface
            {
                return Utils::streamFor($resource);
            }
        };

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $url,
            $apikeyOrUsername,
            $pwd
        );

        $client->$method($path, $data);
    }

    public function createdGetRequestsData()
    {
        return [
            [
                // Test username/password in auth header
                'http://test.local', 'username', 'password',
                'requestGet', '/path', null,
                'GET http://test.local/path 1.1'.\PHP_EOL.
                'Host: test.local'.\PHP_EOL.
                'Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ='.\PHP_EOL.
                \PHP_EOL
            ],
            [
                // Test access token in X-Redmine-API-Key header
                'http://test.local', 'access_token', null,
                'requestGet', '/path', null,
                'GET http://test.local/path 1.1'.\PHP_EOL.
                'Host: test.local'.\PHP_EOL.
                'X-Redmine-API-Key: access_token'.\PHP_EOL.
                \PHP_EOL
            ],
            [
                'http://test.local', 'access_token', null,
                'requestPost', '/path.json', '{"foo":"bar"}',
                'POST http://test.local/path.json 1.1'.\PHP_EOL.
                'Host: test.local'.\PHP_EOL.
                'X-Redmine-API-Key: access_token'.\PHP_EOL.
                'Content-Type: application/json'.\PHP_EOL.
                \PHP_EOL.
                '{"foo":"bar"}'
            ],
        ];
    }
}

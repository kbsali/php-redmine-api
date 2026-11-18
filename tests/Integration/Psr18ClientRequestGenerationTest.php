<?php

declare(strict_types=1);

namespace Redmine\Tests\Integration;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Redmine\Client\Psr18Client;
use Redmine\Http\HttpFactory;

#[CoversClass(Psr18Client::class)]
class Psr18ClientRequestGenerationTest extends TestCase
{
    /**
     * @dataProvider createdGetRequestsData
     */
    #[DataProvider('createdGetRequestsData')]
    public function testPsr18ClientCreatesCorrectRequests(
        string $url,
        string $apikeyOrUsername,
        ?string $pwd,
        ?string $impersonateUser,
        string $method,
        string $path,
        string|bool|null $data,
        string $expectedOutput,
    ): void {
        $response = new Response(200);

        /** @var ClientInterface&\PHPUnit\Framework\MockObject\MockObject */
        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturnCallback(function ($request) use ($response, $expectedOutput): ResponseInterface {
            // Create a text representation of the HTTP request
            $content = $request->getBody()->__toString();

            $headers = '';

            foreach ($request->getHeaders() as $k => $v) {
                $headers .= $k . ': ' . $request->getHeaderLine($k) . \PHP_EOL;
            }

            $statusLine = sprintf(
                '%s %s HTTP/%s',
                $request->getMethod(),
                $request->getUri()->__toString(),
                $request->getProtocolVersion(),
            );

            $fullRequest = $statusLine . \PHP_EOL
                . $headers . \PHP_EOL
                . $content
            ;

            $this->assertSame($expectedOutput, $fullRequest);

            return $response;
        });

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturnCallback(fn($method, $uri): \GuzzleHttp\Psr7\Request => new Request($method, $uri));

        $streamFactory = new class implements StreamFactoryInterface {
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
            $pwd,
        );

        if (null !== $impersonateUser) {
            $client->startImpersonateUser($impersonateUser);
        }

        $client->request(HttpFactory::makeRequest($method, $path, '', (string) $data));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function createdGetRequestsData(): array
    {
        return [
            'Test username/password in auth header' => [
                'http://test.local', 'username', 'password', null,
                'GET', '/path', null,
                'GET http://test.local/path HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'Authorization: Basic dXNlcm5hbWU6cGFzc3dvcmQ=' . \PHP_EOL
                . \PHP_EOL,
            ],
            'Test access token in X-Redmine-API-Key header' => [
                'http://test.local', 'access_token', null, null,
                'GET', '/path', null,
                'GET http://test.local/path HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . \PHP_EOL,
            ],
            'Test user impersonate in X-Redmine-Switch-User header' => [
                'http://test.local', 'access_token', null, 'Robin',
                'GET', '/path', null,
                'GET http://test.local/path HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'X-Redmine-Switch-User: Robin' . \PHP_EOL
                . \PHP_EOL,
            ],
            'Test POST' => [
                'http://test.local', 'access_token', null, null,
                'POST', '/path.json', '{"foo":"bar"}',
                'POST http://test.local/path.json HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'Content-Type: application/json' . \PHP_EOL
                . \PHP_EOL
                . '{"foo":"bar"}',
            ],
            'Test fileupload with file content' => [
                // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#Attaching-files
                'http://test.local', 'access_token', null, null,
                'POST', '/uploads.json?filename=textfile.md', 'The content of the file',
                'POST http://test.local/uploads.json?filename=textfile.md HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'Content-Type: application/octet-stream' . \PHP_EOL
                . \PHP_EOL
                . 'The content of the file',
            ],
            'Test fileupload sends a file path as content as is' => [
                // @see https://www.redmine.org/projects/redmine/wiki/Rest_api#Attaching-files
                'http://test.local', 'access_token', null, null,
                'POST', '/uploads.json?filename=textfile.md', (string) realpath(__DIR__ . '/../Fixtures/testfile_01.txt'),
                'POST http://test.local/uploads.json?filename=textfile.md HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'Content-Type: application/octet-stream' . \PHP_EOL
                . \PHP_EOL
                . realpath(__DIR__ . '/../Fixtures/testfile_01.txt'),
            ],
            'Test PUT' => [
                'http://test.local', 'access_token', null, null,
                'PUT', '/path.json', '{"foo":"bar"}',
                'PUT http://test.local/path.json HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'Content-Type: application/json' . \PHP_EOL
                . \PHP_EOL
                . '{"foo":"bar"}',
            ],
            'Test DELETE' => [
                'http://test.local', 'access_token', null, null,
                'DELETE', '/path.json', '{"foo":"bar"}',
                'DELETE http://test.local/path.json HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'Content-Type: application/json' . \PHP_EOL
                . \PHP_EOL,
            ],
            'Test body will be ignored on DELETE' => [
                'http://test.local', 'access_token', null, null,
                'DELETE', '/path.json', '{"foo":"bar"}',
                'DELETE http://test.local/path.json HTTP/1.1' . \PHP_EOL
                . 'Host: test.local' . \PHP_EOL
                . 'X-Redmine-API-Key: access_token' . \PHP_EOL
                . 'Content-Type: application/json' . \PHP_EOL
                . \PHP_EOL,
            ],
        ];
    }
}

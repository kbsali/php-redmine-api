<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\NativeCurlClientTest;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\Request;
use Redmine\Http\Response;

#[CoversClass(NativeCurlClient::class)]
class RequestTest extends TestCase
{
    use PHPMock;

    /**
     * @dataProvider getRequestReponseData
     */
    #[DataProvider('getRequestReponseData')]
    public function testRequestReturnsCorrectResponse(string $method, string $data, int $statusCode, string $contentType, string $content): void
    {
        $namespace = 'Redmine\Client';

        $curl = curl_init();

        $curlInit = $this->getFunctionMock($namespace, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock($namespace, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn($content);

        $this->getFunctionMock($namespace, 'curl_setopt_array');

        $curlGetinfo = $this->getFunctionMock($namespace, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(2))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, $statusCode],
            [$curl, CURLINFO_CONTENT_TYPE, $contentType],
        ]));

        $curlErrno = $this->getFunctionMock($namespace, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(CURLE_OK);

        $this->getFunctionMock($namespace, 'curl_close');

        $client = new NativeCurlClient(
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
            ['GET', '', 301, 'application/json', ''],
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

    public function testRequestWithUploadSendsFilepathAsContentAsIs(): void
    {
        $namespace = 'Redmine\Client';

        $curl = curl_init();

        $curlInit = $this->getFunctionMock($namespace, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock($namespace, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn('{"upload":{}}');

        $filepath = (string) realpath(__DIR__ . '/../../../Fixtures/testfile_01.txt');

        $curlSetoptArray = $this->getFunctionMock($namespace, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(1))->willReturnCallback(function ($handle, array $options) use ($filepath): bool {
            $this->assertSame($filepath, $options[CURLOPT_POSTFIELDS]);
            $this->assertArrayNotHasKey(CURLOPT_INFILE, $options);
            $this->assertArrayNotHasKey(CURLOPT_INFILESIZE, $options);

            return true;
        });

        $curlGetinfo = $this->getFunctionMock($namespace, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(2))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 201],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlErrno = $this->getFunctionMock($namespace, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(CURLE_OK);

        $this->getFunctionMock($namespace, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $request = $this->createStub(Request::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getPath')->willReturn('/uploads.json');
        $request->method('getContentType')->willReturn('application/octet-stream');
        $request->method('getContent')->willReturn($filepath);

        $response = $client->request($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getContentType());
        $this->assertSame('{"upload":{}}', $response->getContent());
    }

    /**
     * @dataProvider getRequestHeaderData
     *
     * @param array<string> $expectedHeaders
     */
    #[DataProvider('getRequestHeaderData')]
    public function testRequestSendsCorrectHeaders(?string $password, ?string $impersonateUser, string $path, string $contentType, array $expectedHeaders): void
    {
        $namespace = 'Redmine\Client';

        $curl = curl_init();

        $curlInit = $this->getFunctionMock($namespace, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock($namespace, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn('');

        $curlSetoptArray = $this->getFunctionMock($namespace, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(1))->willReturnCallback(function ($handle, array $options) use ($expectedHeaders): bool {
            $this->assertSame($expectedHeaders, $options[CURLOPT_HTTPHEADER]);

            return true;
        });

        $curlGetinfo = $this->getFunctionMock($namespace, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(2))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, ''],
        ]));

        $curlErrno = $this->getFunctionMock($namespace, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(CURLE_OK);

        $this->getFunctionMock($namespace, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'username',
            $password,
        );

        if ($impersonateUser !== null) {
            $client->startImpersonateUser($impersonateUser);
        }

        $request = $this->createStub(Request::class);
        $request->method('getMethod')->willReturn('GET');
        $request->method('getPath')->willReturn($path);
        $request->method('getContentType')->willReturn($contentType);
        $request->method('getContent')->willReturn('');

        $client->request($request);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getRequestHeaderData(): array
    {
        return [
            'api key' => [null, null, '/path', '', ['Expect: ', 'X-Redmine-API-Key: username']],
            'basic auth' => ['secret', null, '/path', '', ['Expect: ', 'Authorization: Basic ' . base64_encode('username:secret')]],
            'impersonate user' => [null, 'jdoe', '/path', '', ['Expect: ', 'X-Redmine-Switch-User: jdoe', 'X-Redmine-API-Key: username']],
            'content type from request' => [null, null, '/path', 'application/json', ['Expect: ', 'X-Redmine-API-Key: username', 'Content-Type: application/json']],
            'content type guessed from json path' => [null, null, '/issues.json', '', ['Expect: ', 'X-Redmine-API-Key: username', 'Content-Type: application/json']],
            'content type guessed from xml path' => [null, null, '/issues.xml', '', ['Expect: ', 'X-Redmine-API-Key: username', 'Content-Type: text/xml']],
            'content type guessed from upload path' => [null, null, '/uploads.json', '', ['Expect: ', 'X-Redmine-API-Key: username', 'Content-Type: application/octet-stream']],
            'no content type guessed' => [null, null, '/path', '', ['Expect: ', 'X-Redmine-API-Key: username']],
        ];
    }
}

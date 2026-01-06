<?php

namespace Redmine\Tests\Unit\Client\NativeCurlClientTest;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\Request;
use Redmine\Http\Response;
use stdClass;

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

        $curl = $this->createStub(stdClass::class);

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

    public function testRequestWithUploadAndFilepathReturnsCorrectResponse(): void
    {
        $namespace = 'Redmine\Client';

        $curl = $this->createStub(stdClass::class);

        $curlInit = $this->getFunctionMock($namespace, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock($namespace, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn('{"upload":{}}');

        $this->getFunctionMock($namespace, 'curl_setopt_array');

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

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    'Uploading an attachment by filepath is deprecated since v2.1.0, use file_get_contents() to upload the file content instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $request = $this->createStub(Request::class);
        $request->method('getMethod')->willReturn('POST');
        $request->method('getPath')->willReturn('/uploads.json');
        $request->method('getContentType')->willReturn('application/octet-stream');
        $request->method('getContent')->willReturn(realpath(__DIR__ . '/../../../Fixtures/testfile_01.txt'));

        $response = $client->request($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('application/json', $response->getContentType());
        $this->assertSame('{"upload":{}}', $response->getContent());
    }
}

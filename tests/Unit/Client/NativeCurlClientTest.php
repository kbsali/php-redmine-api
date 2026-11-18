<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client;

use Exception;
use InvalidArgumentException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Client\Client;
use Redmine\Client\NativeCurlClient;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;

#[CoversClass(NativeCurlClient::class)]
class NativeCurlClientTest extends TestCase
{
    use PHPMock;

    public const __NAMESPACE__ = 'Redmine\Client';

    public const DEFAULT_CURL_OPTIONS = [
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_PORT => 80,
        CURLOPT_URL => 'http://test.local/path',
        CURLOPT_HTTPHEADER => [
            'Expect: ',
            'X-Redmine-API-Key: access_token',
        ],
        CURLOPT_VERBOSE => 0,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
    ];

    public function testApiKeyShouldBePassToConstructor(): void
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf(NativeCurlClient::class, $client);
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testShouldPassUsernameAndPasswordToConstructor(): void
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'username',
            'password',
        );

        $this->assertInstanceOf(NativeCurlClient::class, $client);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testStartAndStopImpersonateUser(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 80,
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-Switch-User: Sam',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->startImpersonateUser('Sam');
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->stopImpersonateUser();
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetSslVersion(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
            CURLOPT_PORT => 80,
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->setCurlOption(CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_2_0);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->unsetCurlOption(CURLOPT_HTTP_VERSION);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetSslVerifypeer(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 80,
            CURLOPT_SSL_VERIFYPEER => 1,
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->setCurlOption(CURLOPT_SSL_VERIFYPEER, 1);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->unsetCurlOption(CURLOPT_SSL_VERIFYPEER);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetSslVerifyhost(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 80,
            CURLOPT_SSL_VERIFYHOST => 2, // @see http://curl.haxx.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->setCurlOption(CURLOPT_SSL_VERIFYHOST, 2);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->unsetCurlOption(CURLOPT_SSL_VERIFYHOST);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetCustomHttpHeaders(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 80,
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
                'FooBar: test case sensitivity',
                'DOUBLE: This will override the previous header',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->setCurlOption(CURLOPT_HTTPHEADER, [
            'FooBar: test case sensitivity',
            'double: This will be overridden',
            'DOUBLE: This will override the previous header',
            'invalid-header-will-be-ignored',
        ]);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->unsetCurlOption(CURLOPT_HTTPHEADER);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetCustomHost(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 80,
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
                'Host: http://custom.example.com',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->setCurlOption(CURLOPT_HTTPHEADER, [
            'Host: http://custom.example.com',
        ]);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->unsetCurlOption(CURLOPT_HTTPHEADER);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetPort(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 8080,
            CURLOPT_URL => 'http://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->willReturnMap([
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
                [$this->anything(), $this->identicalTo($expectedOptions), true],
                [$this->anything(), $this->identicalTo(self::DEFAULT_CURL_OPTIONS), true],
            ])
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(3))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->setCurlOption(CURLOPT_PORT, 8080);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->unsetCurlOption(CURLOPT_PORT);
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testSetCurlOptionHttpheaderWithoutArrayThrowsException(): void
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $this->expectException(\Throwable::class);
        $this->expectExceptionMessage('If argument #1 ($option) passed to Redmine\Client\NativeCurlClient::setCurlOption() is set to `CURLOPT_HTTPHEADER` (10023), then Argument #2 ($value) must be of the type array, but `string` given');

        $client->setCurlOption(CURLOPT_HTTPHEADER, 'this MUST be an array.');
    }

    public function testCustomPortWillSetFromSchema(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 443,
            CURLOPT_URL => 'https://test.local/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(2))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(1))
            ->with(
                $this->anything(),
                $this->identicalTo($expectedOptions),
            )
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'https://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testCustomPortWillSetFromUrl(): void
    {
        $expectedOptions = [
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_PORT => 3456,
            CURLOPT_URL => 'http://test.local:3456/path',
            CURLOPT_HTTPHEADER => [
                'Expect: ',
                'X-Redmine-API-Key: access_token',
            ],
            CURLOPT_VERBOSE => 0,
            CURLOPT_HEADER => 0,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(2))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ]));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(1))
            ->with(
                $this->anything(),
                $this->identicalTo($expectedOptions),
            )
        ;

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(0);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local:3456',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    public function testHandlingOfResponseWithoutContent(): void
    {
        $content = '';
        $statusCode = 204;
        $contentType = null;

        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn('');

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(2))->willReturnMap(([
            [$curl, CURLINFO_HTTP_CODE, $statusCode],
            [$curl, CURLINFO_CONTENT_TYPE, $contentType],
        ]));

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(CURLE_OK);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $response = $client->request(HttpFactory::makeRequest('PUT', '/path', '', '{"foo":"bar"}'));

        $this->assertSame($statusCode, $response->getStatusCode());
        $this->assertSame('', $response->getContentType());
        $this->assertSame($content, $response->getContent());
    }

    public function testCurlErrorThrowsException(): void
    {
        $curl = curl_init();

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(1))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(1))->willReturn(false);

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');

        $curlErrno = $this->getFunctionMock(self::__NAMESPACE__, 'curl_errno');
        $curlErrno->expects($this->exactly(1))->willReturn(CURLE_URL_MALFORMAT);

        $curlError = $this->getFunctionMock(self::__NAMESPACE__, 'curl_error');
        $curlError->expects($this->exactly(1))->willReturn('cURL error 3: <url> malformed');

        $this->getFunctionMock(self::__NAMESPACE__, 'curl_close');

        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('cURL error 3: <url> malformed');

        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    /**
     * @dataProvider getApiClassesProvider
     */
    #[DataProvider('getApiClassesProvider')]
    public function testGetApiReturnsApiInstance(string $apiName, string $class): void
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf($class, $client->getApi($apiName));
    }

    /**
     * @return array<array{string, class-string}>
     */
    public static function getApiClassesProvider(): array
    {
        return [
            ['attachment', \Redmine\Api\Attachment::class],
            ['group', \Redmine\Api\Group::class],
            ['custom_fields', \Redmine\Api\CustomField::class],
            ['issue', \Redmine\Api\Issue::class],
            ['issue_category', \Redmine\Api\IssueCategory::class],
            ['issue_priority', \Redmine\Api\IssuePriority::class],
            ['issue_relation', \Redmine\Api\IssueRelation::class],
            ['issue_status', \Redmine\Api\IssueStatus::class],
            ['membership', \Redmine\Api\Membership::class],
            ['news', \Redmine\Api\News::class],
            ['project', \Redmine\Api\Project::class],
            ['query', \Redmine\Api\Query::class],
            ['role', \Redmine\Api\Role::class],
            ['time_entry', \Redmine\Api\TimeEntry::class],
            ['time_entry_activity', \Redmine\Api\TimeEntryActivity::class],
            ['tracker', \Redmine\Api\Tracker::class],
            ['user', \Redmine\Api\User::class],
            ['version', \Redmine\Api\Version::class],
            ['wiki', \Redmine\Api\Wiki::class],
        ];
    }

    /**
     * @dataProvider getApiClassesProvider
     */
    #[DataProvider('getApiClassesProvider')]
    public function testGetApiOnMultipleCallsReturnSameApiInstance(string $apiName, string $class): void
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $this->assertSame(
            $client->getApi($apiName),
            $client->getApi($apiName),
        );
    }

    public function testGetApiWithInvalidApiThrowsException(): void
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`do_not_exist` is not a valid api. Possible apis are `attachment`, `group`, `custom_fields`, `issue`, `issue_category`, `issue_priority`, `issue_relation`, `issue_status`, `membership`, `news`, `project`, `query`, `role`, `search`, `time_entry`, `time_entry_activity`, `tracker`, `user`, `version`, `wiki`');

        $client->getApi('do_not_exist');
    }
}

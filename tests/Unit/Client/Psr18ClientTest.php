<?php

namespace Redmine\Tests\Unit\Client;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Redmine\Client\Client;
use Redmine\Client\Psr18Client;
use Redmine\Exception\ClientException;
use Redmine\Http\HttpClient;
use stdClass;

#[CoversClass(Psr18Client::class)]
class Psr18ClientTest extends TestCase
{
    public function testShouldPassApiKeyToConstructor(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testServerRequestFactoryIsAcceptedInConstructorForBC(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createConfiguredMock(ServerRequestFactoryInterface::class, [
                'createServerRequest' => (function (): \PHPUnit\Framework\MockObject\MockObject {
                    $request = $this->createMock(ServerRequestInterface::class);
                    $request->method('withHeader')->willReturn($request);
                    $request->method('withBody')->willReturn($request);

                    return $request;
                })(),
            ]),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);

        $client->requestGet('/path.xml');
    }

    public function testShouldPassUsernameAndPasswordToConstructor(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'username',
            'password',
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function testGetLastResponseStatusCodeIsInitialZero(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertSame(0, $client->getLastResponseStatusCode());
    }

    public function testGetLastResponseStatusCodeTriggersDeprecationWarning(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::getLastResponseStatusCode()` is deprecated since v2.8.0, use `\Redmine\Api\AbstractApi::getLastResponse()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $client->getLastResponseStatusCode();
    }

    public function testGetLastResponseContentTypeIsInitialEmpty(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertSame('', $client->getLastResponseContentType());
    }

    public function testGetLastResponseContentTypeTriggersDeprecationWarning(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::getLastResponseContentType()` is deprecated since v2.8.0, use `\Redmine\Api\AbstractApi::getLastResponse()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $client->getLastResponseContentType();
    }

    public function testGetLastResponseBodyIsInitialEmpty(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertSame('', $client->getLastResponseBody());
    }

    public function testGetLastResponseBodyTriggersDeprecationWarning(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::getLastResponseBody()` is deprecated since v2.8.0, use `\Redmine\Api\AbstractApi::getLastResponse()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $client->getLastResponseBody();
    }

    public function testStartAndStopImpersonateUser(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->exactly(4))
            ->method('withHeader')
            ->willReturnMap([
                ['X-Redmine-API-Key', 'access_token', $request],
                ['X-Redmine-API-Key', 'access_token', $request],
                ['X-Redmine-Switch-User', 'Sam', $request],
                ['X-Redmine-API-Key', 'access_token', $request],
            ]);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $client->requestGet('/path');
        $client->startImpersonateUser('Sam');
        $client->requestGet('/path');
        $client->stopImpersonateUser();
        $client->requestGet('/path');
    }

    public function testRequestGetReturnsFalse(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertSame(false, $client->requestGet('/path'));
    }

    /**
     * @dataProvider getRequestReponseData
     */
    #[DataProvider('getRequestReponseData')]
    public function testRequestsReturnsCorrectContent(string $method, string $data, bool $boolReturn, int $statusCode, string $contentType, string $content): void
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getHeaderLine')->willReturn($contentType);
        $response->method('getBody')->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $request = $this->createMock(RequestInterface::class);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertSame($boolReturn, $client->$method('/path', $data));
        $this->assertSame($statusCode, $client->getLastResponseStatusCode());
        $this->assertSame($contentType, $client->getLastResponseContentType());
        $this->assertSame($content, $client->getLastResponseBody());
    }

    public static function getRequestReponseData(): array
    {
        return [
            ['requestGet', '', true, 101, 'text/plain', ''],
            ['requestGet', '', true, 200, 'application/json', '{"foo_bar": 12345}'],
            ['requestGet', '', true, 301, 'application/json', ''],
            ['requestGet', '', false, 404, 'application/json', '{"title": "404 Not Found"}'],
            ['requestGet', '', false, 500, 'text/plain', 'Internal Server Error'],
            ['requestPost', '{"foo":"bar"}', true, 101, 'text/plain', ''],
            ['requestPost', '{"foo":"bar"}', true, 200, 'application/json', '{"foo_bar": 12345}'],
            ['requestPost', '{"foo":"bar"}', true, 301, 'application/json', ''],
            ['requestPost', '{"foo":"bar"}', false, 404, 'application/json', '{"title": "404 Not Found"}'],
            ['requestPost', '{"foo":"bar"}', false, 500, 'text/plain', 'Internal Server Error'],
            ['requestPut', '{"foo":"bar"}', true, 101, 'text/plain', ''],
            ['requestPut', '{"foo":"bar"}', true, 200, 'application/json', '{"foo_bar": 12345}'],
            ['requestPut', '{"foo":"bar"}', true, 301, 'application/json', ''],
            ['requestPut', '{"foo":"bar"}', false, 404, 'application/json', '{"title": "404 Not Found"}'],
            ['requestPut', '{"foo":"bar"}', false, 500, 'text/plain', 'Internal Server Error'],
            ['requestDelete', '', true, 101, 'text/plain', ''],
            ['requestDelete', '', true, 200, 'application/json', '{"foo_bar": 12345}'],
            ['requestDelete', '', true, 301, 'application/json', ''],
            ['requestDelete', '', false, 404, 'application/json', '{"title": "404 Not Found"}'],
            ['requestDelete', '', false, 500, 'text/plain', 'Internal Server Error'],
        ];
    }

    public function testRequestGetTriggersDeprecationWarning(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willThrowException(
            $this->createMock(ClientException::class),
        );

        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::requestGet()` is deprecated since v2.8.0, use `\Redmine\Client\Psr18Client::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        try {
            $client->requestGet('/path');
        } catch (ClientException $th) {
        }
    }

    public function testRequestPostTriggersDeprecationWarning(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willThrowException(
            $this->createMock(ClientException::class),
        );

        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::requestPost()` is deprecated since v2.8.0, use `\Redmine\Client\Psr18Client::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        try {
            $client->requestPost('/path', '');
        } catch (ClientException $th) {
        }
    }

    public function testRequestPutTriggersDeprecationWarning(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willThrowException(
            $this->createMock(ClientException::class),
        );

        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::requestPut()` is deprecated since v2.8.0, use `\Redmine\Client\Psr18Client::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        try {
            $client->requestPut('/path', '');
        } catch (ClientException $th) {
        }
    }

    public function testRequestDeleteTriggersDeprecationWarning(): void
    {
        $requestFactory = $this->createMock(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willThrowException(
            $this->createMock(ClientException::class),
        );

        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Client\Psr18Client::requestDelete()` is deprecated since v2.8.0, use `\Redmine\Client\Psr18Client::request()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        try {
            $client->requestDelete('/path');
        } catch (ClientException $th) {
        }
    }

    /**
     * @dataProvider getApiClassesProvider
     */
    #[DataProvider('getApiClassesProvider')]
    public function testGetApiShouldReturnApiInstance(string $apiName, string $class): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf($class, $client->getApi($apiName));
    }

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

    public function testCreateWithoutFactoryThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Redmine\Client\Psr18Client::__construct(): Argument #2 ($requestFactory) must be of type Psr\Http\Message\RequestFactoryInterface');

        new Psr18Client(
            $this->createMock(ClientInterface::class),
            /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
            new stdClass(),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );
    }

    public function testGetApiShouldThrowException(): void
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(RequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`do_not_exist` is not a valid api. Possible apis are `attachment`, `group`, `custom_fields`, `issue`, `issue_category`, `issue_priority`, `issue_relation`, `issue_status`, `membership`, `news`, `project`, `query`, `role`, `search`, `time_entry`, `time_entry_activity`, `tracker`, `user`, `version`, `wiki`');

        $client->getApi('do_not_exist');
    }
}

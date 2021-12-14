<?php

namespace Redmine\Tests\Unit\Client;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Redmine\Client\Client;
use Redmine\Client\Psr18Client;

class Psr18ClientTest extends TestCase
{
    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function shouldPassApiKeyToConstructor()
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function shouldPassUsernameAndPasswordToConstructor()
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'username',
            'password'
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function testGetLastResponseStatusCodeIsInitialNull()
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame(0, $client->getLastResponseStatusCode());
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function testGetLastResponseContentTypeIsInitialEmpty()
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame('', $client->getLastResponseContentType());
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function testGetLastResponseBodyIsInitialEmpty()
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame('', $client->getLastResponseBody());
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function testStartAndStopImpersonateUser()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->exactly(4))
            ->method('withHeader')
            ->withConsecutive(
                ['X-Redmine-API-Key', 'access_token'],
                ['X-Redmine-API-Key', 'access_token'],
                ['X-Redmine-Switch-User', 'Sam'],
                ['X-Redmine-API-Key', 'access_token'],
            )
            ->willReturn($request);

        $requestFactory = $this->createMock(ServerRequestFactoryInterface::class);
        $requestFactory->method('createServerRequest')->willReturn($request);

        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $client->requestGet('/path');
        $client->startImpersonateUser('Sam');
        $client->requestGet('/path');
        $client->stopImpersonateUser();
        $client->requestGet('/path');
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function testRequestGetReturnsFalse()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('withHeader')->willReturn($request);

        $requestFactory = $this->createMock(ServerRequestFactoryInterface::class);
        $requestFactory->method('createServerRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame(false, $client->requestGet('/path'));
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     * @dataProvider getRequestReponseData
     */
    public function testRequestsReturnsCorrectContent($method, $data, $boolReturn, $statusCode, $contentType, $content)
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($content);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn($statusCode);
        $response->method('getHeaderLine')->willReturn($contentType);
        $response->method('getBody')->willReturn($stream);

        $httpClient = $this->createMock(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('withHeader')->willReturn($request);
        $request->method('withBody')->willReturn($request);

        $requestFactory = $this->createMock(ServerRequestFactoryInterface::class);
        $requestFactory->method('createServerRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame($boolReturn, $client->$method('/path', $data));
        $this->assertSame($statusCode, $client->getLastResponseStatusCode());
        $this->assertSame($contentType, $client->getLastResponseContentType());
        $this->assertSame($content, $client->getLastResponseBody());
    }

    public function getRequestReponseData()
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

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     *
     * @param string $apiName
     * @param string $class
     * @dataProvider getApiClassesProvider
     */
    public function getApiShouldReturnApiInstance($apiName, $class)
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertInstanceOf($class, $client->getApi($apiName));
    }

    public function getApiClassesProvider()
    {
        return [
            ['attachment', 'Redmine\Api\Attachment'],
            ['group', 'Redmine\Api\Group'],
            ['custom_fields', 'Redmine\Api\CustomField'],
            ['issue', 'Redmine\Api\Issue'],
            ['issue_category', 'Redmine\Api\IssueCategory'],
            ['issue_priority', 'Redmine\Api\IssuePriority'],
            ['issue_relation', 'Redmine\Api\IssueRelation'],
            ['issue_status', 'Redmine\Api\IssueStatus'],
            ['membership', 'Redmine\Api\Membership'],
            ['news', 'Redmine\Api\News'],
            ['project', 'Redmine\Api\Project'],
            ['query', 'Redmine\Api\Query'],
            ['role', 'Redmine\Api\Role'],
            ['time_entry', 'Redmine\Api\TimeEntry'],
            ['time_entry_activity', 'Redmine\Api\TimeEntryActivity'],
            ['tracker', 'Redmine\Api\Tracker'],
            ['user', 'Redmine\Api\User'],
            ['version', 'Redmine\Api\Version'],
            ['wiki', 'Redmine\Api\Wiki'],
        ];
    }

    /**
     * @covers \Redmine\Client\Psr18Client
     * @test
     */
    public function getApiShouldThrowException()
    {
        $client = new Psr18Client(
            $this->createMock(ClientInterface::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`do_not_exist` is not a valid api. Possible apis are `attachment`, `group`, `custom_fields`, `issue`, `issue_category`, `issue_priority`, `issue_relation`, `issue_status`, `membership`, `news`, `project`, `query`, `role`, `time_entry`, `time_entry_activity`, `tracker`, `user`, `version`, `wiki`, `search`');

        $client->getApi('do_not_exist');
    }
}

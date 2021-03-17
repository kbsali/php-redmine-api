<?php

namespace Redmine\Tests\Unit;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Api\ApiInterface;
use Redmine\Psr18Client;
use Redmine\ClientInterface;

class Psr18ClientTest extends TestCase
{
    /**
     * @covers \Redmine\Psr18Client
     * @test
     */
    public function shouldPassApiKeyToConstructor()
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    /**
     * @covers \Redmine\Psr18Client
     * @test
     */
    public function shouldPassUsernameAndPasswordToConstructor()
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'username',
            'password'
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    /**
     * @covers \Redmine\Psr18Client
     * @test
     */
    public function testGetLastResponseStatusCodeIsInitialNull()
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame(0, $client->getLastResponseStatusCode());
    }

    /**
     * @covers \Redmine\Psr18Client
     * @test
     */
    public function testGetLastResponseContentTypeIsInitialEmpty()
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame('', $client->getLastResponseContentType());
    }

    /**
     * @covers \Redmine\Psr18Client
     * @test
     */
    public function testGetLastResponseBodyIsInitialEmpty()
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        $this->assertSame('', $client->getLastResponseBody());
    }

    /**
     * @covers \Redmine\Psr18Client
     * @test
     */
    public function testRequestGetReturnsFalse()
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);

        $httpClient = $this->createMock(HttpClient::class);
        $httpClient->method('sendRequest')->willReturn($response);

        $client = new Psr18Client(
            $httpClient,
            $this->createMock(ServerRequestFactoryInterface::class),
            $this->createMock(StreamFactoryInterface::class),
            'http://test.local',
            'access_token'
        );

        // Perform the tests
        $this->assertSame(false, $client->requestGet('path'));
    }

    /**
     * @covers \Redmine\Psr18Client
     * @test
     *
     * @param string $apiName
     * @param string $class
     * @dataProvider getApiClassesProvider
     */
    public function getApiShouldReturnApiInstance($apiName, $class)
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
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
     * @covers \Redmine\Psr18Client
     * @test
     *
     */
    public function getApiShouldThrowException()
    {
        $client = new Psr18Client(
            $this->createMock(HttpClient::class),
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

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client;

use Exception;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Client\Client;
use Redmine\Client\Psr18Client;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use stdClass;

#[CoversClass(Psr18Client::class)]
final class Psr18ClientTest extends TestCase
{
    public function testShouldPassApiKeyToConstructor(): void
    {
        $client = new Psr18Client(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(HttpClient::class, $client);
    }

    public function testServerRequestFactoryIsNotAcceptedInConstructor(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Redmine\Client\Psr18Client::__construct(): Argument #2 ($requestFactory) must be of type Psr\Http\Message\RequestFactoryInterface');

        new Psr18Client(
            $this->createStub(ClientInterface::class),
            /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
            $this->createStub(ServerRequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );
    }

    public function testShouldPassUsernameAndPasswordToConstructor(): void
    {
        $client = new Psr18Client(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'username',
            'password',
        );

        $this->assertInstanceOf(Psr18Client::class, $client);
        $this->assertInstanceOf(Client::class, $client);
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

        $httpClient = $this->createStub(ClientInterface::class);
        $httpClient->method('sendRequest')->willReturn(new Response(200));

        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $requestFactory->method('createRequest')->willReturn($request);

        $client = new Psr18Client(
            $httpClient,
            $requestFactory,
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->startImpersonateUser('Sam');
        $client->request(HttpFactory::makeRequest('GET', '/path'));
        $client->stopImpersonateUser();
        $client->request(HttpFactory::makeRequest('GET', '/path'));
    }

    #[DataProvider('getApiClassesProvider')]
    public function testGetApiShouldReturnApiInstance(string $apiName, string $class): void
    {
        $client = new Psr18Client(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->assertInstanceOf($class, $client->getApi($apiName));
    }

    /**
     * @return \Iterator<int|string, array{string, class-string}>
     */
    public static function getApiClassesProvider(): \Iterator
    {
        yield ['attachment', \Redmine\Api\Attachment::class];
        yield ['group', \Redmine\Api\Group::class];
        yield ['custom_fields', \Redmine\Api\CustomField::class];
        yield ['issue', \Redmine\Api\Issue::class];
        yield ['issue_category', \Redmine\Api\IssueCategory::class];
        yield ['issue_priority', \Redmine\Api\IssuePriority::class];
        yield ['issue_relation', \Redmine\Api\IssueRelation::class];
        yield ['issue_status', \Redmine\Api\IssueStatus::class];
        yield ['membership', \Redmine\Api\Membership::class];
        yield ['news', \Redmine\Api\News::class];
        yield ['project', \Redmine\Api\Project::class];
        yield ['query', \Redmine\Api\Query::class];
        yield ['role', \Redmine\Api\Role::class];
        yield ['time_entry', \Redmine\Api\TimeEntry::class];
        yield ['time_entry_activity', \Redmine\Api\TimeEntryActivity::class];
        yield ['tracker', \Redmine\Api\Tracker::class];
        yield ['user', \Redmine\Api\User::class];
        yield ['version', \Redmine\Api\Version::class];
        yield ['wiki', \Redmine\Api\Wiki::class];
    }

    public function testCreateWithoutFactoryThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Redmine\Client\Psr18Client::__construct(): Argument #2 ($requestFactory) must be of type Psr\Http\Message\RequestFactoryInterface');

        new Psr18Client(
            $this->createStub(ClientInterface::class),
            /** @phpstan-ignore-next-line We are providing an invalid parameter to test the exception */
            new stdClass(),
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );
    }

    public function testGetApiShouldThrowException(): void
    {
        $client = new Psr18Client(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            'http://test.local',
            'access_token',
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`do_not_exist` is not a valid api. Possible apis are `attachment`, `group`, `custom_fields`, `issue`, `issue_category`, `issue_priority`, `issue_relation`, `issue_status`, `membership`, `news`, `project`, `query`, `role`, `search`, `time_entry`, `time_entry_activity`, `tracker`, `user`, `version`, `wiki`');

        $client->getApi('do_not_exist');
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client;

use InvalidArgumentException;
use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Api;
use Redmine\Client\NativeCurlClient;
use Redmine\Client\Client;
use stdClass;

class NativeCurlClientTest extends TestCase
{
    use PHPMock;

    const __NAMESPACE__ = 'Redmine\Client';

    /**
     * @covers \Redmine\NativeCurlClient
     * @test
     */
    public function shouldPassApiKeyToConstructor()
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token'
        );

        $this->assertInstanceOf(NativeCurlClient::class, $client);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @covers \Redmine\NativeCurlClient
     * @test
     */
    public function shouldPassUsernameAndPasswordToConstructor()
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'username',
            'password'
        );

        $this->assertInstanceOf(NativeCurlClient::class, $client);
        $this->assertInstanceOf(Client::class, $client);
    }

    /**
     * @covers \Redmine\NativeCurlClient
     * @test
     */
    public function testGetLastResponseStatusCodeIsInitialNull()
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token'
        );

        $this->assertSame(0, $client->getLastResponseStatusCode());
    }

    /**
     * @covers \Redmine\NativeCurlClient
     * @test
     */
    public function testGetLastResponseContentTypeIsInitialEmpty()
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token'
        );

        $this->assertSame('', $client->getLastResponseContentType());
    }

    /**
     * @covers \Redmine\NativeCurlClient
     * @test
     */
    public function testGetLastResponseBodyIsInitialEmpty()
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token'
        );

        $this->assertSame('', $client->getLastResponseBody());
    }

    /**
     * @covers \Redmine\NativeCurlClient
     * @test
     */
    public function testStartAndStopImpersonateUser()
    {
        $curl = $this->createMock(stdClass::class);

        $curlInit = $this->getFunctionMock(self::__NAMESPACE__, 'curl_init');
        $curlInit->expects($this->exactly(3))->willReturn($curl);

        $curlExec = $this->getFunctionMock(self::__NAMESPACE__, 'curl_exec');
        $curlExec->expects($this->exactly(3))->willReturn('');

        $curlGetinfo = $this->getFunctionMock(self::__NAMESPACE__, 'curl_getinfo');
        $curlGetinfo->expects($this->exactly(6))->will($this->returnValueMap(([
            [$curl, CURLINFO_HTTP_CODE, 200],
            [$curl, CURLINFO_CONTENT_TYPE, 'application/json'],
        ])));

        $curlSetoptArray = $this->getFunctionMock(self::__NAMESPACE__, 'curl_setopt_array');
        $curlSetoptArray->expects($this->exactly(3))
            ->withConsecutive(
                [
                    $this->anything(),
                    $this->identicalTo([
                            41 => 0,
                            42 => 0,
                            19913 => 1,
                            84 => 2,
                            10005 => 'access_token:199999',
                            107 => 1,
                            10002 => 'http://test.local/path',
                            3 => 80,
                            10023 => [
                                0 => 'Expect: ',
                                1 => 'X-Redmine-API-Key: access_token',
                            ]
                    ]),
                ],
                [
                    $this->anything(),
                    $this->identicalTo([
                            41 => 0,
                            42 => 0,
                            19913 => 1,
                            84 => 2,
                            10005 => 'access_token:199999',
                            107 => 1,
                            10002 => 'http://test.local/path',
                            3 => 80,
                            10023 => [
                                0 => 'Expect: ',
                                1 => 'X-Redmine-Switch-User: Sam',
                                2 => 'X-Redmine-API-Key: access_token',
                            ]
                    ]),
                ],
                [
                    $this->anything(),
                    $this->identicalTo([
                            41 => 0,
                            42 => 0,
                            19913 => 1,
                            84 => 2,
                            10005 => 'access_token:199999',
                            107 => 1,
                            10002 => 'http://test.local/path',
                            3 => 80,
                            10023 => [
                                0 => 'Expect: ',
                                1 => 'X-Redmine-API-Key: access_token',
                            ]
                    ]),
                ],
            );

        $client = new NativeCurlClient(
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
     * @covers \Redmine\NativeCurlClient
     * @test
     *
     * @param string $apiName
     * @param string $class
     * @dataProvider getApiClassesProvider
     */
    public function getApiShouldReturnApiInstance($apiName, $class)
    {
        $client = new NativeCurlClient(
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
     * @covers \Redmine\NativeCurlClient
     * @test
     *
     */
    public function getApiShouldThrowException()
    {
        $client = new NativeCurlClient(
            'http://test.local',
            'access_token'
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('`do_not_exist` is not a valid api. Possible apis are `attachment`, `group`, `custom_fields`, `issue`, `issue_category`, `issue_priority`, `issue_relation`, `issue_status`, `membership`, `news`, `project`, `query`, `role`, `time_entry`, `time_entry_activity`, `tracker`, `user`, `version`, `wiki`, `search`');

        $client->getApi('do_not_exist');
    }
}

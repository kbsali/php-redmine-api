<?php

namespace Redmine\Tests\Unit\Client;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Api;
use Redmine\Client\NativeCurlClient;
use Redmine\Client\Client;

class NativeCurlClientTest extends TestCase
{
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
}

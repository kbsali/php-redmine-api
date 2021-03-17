<?php

namespace Redmine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Psr18Client;

class Psr18ClientTest extends TestCase
{
    /**
     * @covers \Redmine\Psr18Client
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
    }

    /**
     * @covers \Redmine\Psr18Client
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
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\Psr18Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Client\Psr18Client;

#[CoversClass(Psr18Client::class)]
final class EnableFutureModeTest extends TestCase
{
    public function testEnableFutureModeDoesNothing(): void
    {
        $client = $this->createClient();

        $api = $client->getApi('issue');

        $client->enableFutureMode();

        self::assertSame($api, $client->getApi('issue'));
    }

    private function createClient(): Psr18Client
    {
        return new Psr18Client(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            '',
            '',
        );
    }
}

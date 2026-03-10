<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\Psr18Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Client\Psr18Client;
use Redmine\Future;

#[CoversClass(Psr18Client::class)]
final class EnableFutureModeTest extends TestCase
{
    public function testEnableFutureModeEnablesFutureMode(): void
    {
        Future::disableForwardCompatibility();

        $client = new Psr18Client(
            $this->createStub(ClientInterface::class),
            $this->createStub(RequestFactoryInterface::class),
            $this->createStub(StreamFactoryInterface::class),
            '',
            '',
        );
        $client->enableFutureMode();

        self::assertTrue(Future::isForwardCompatibilityEnabled());

        Future::disableForwardCompatibility();
    }
}

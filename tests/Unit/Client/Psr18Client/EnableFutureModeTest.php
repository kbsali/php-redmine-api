<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\Psr18Client;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Redmine\Api\AbstractApi;
use Redmine\Client\Psr18Client;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Psr18Client::class)]
final class EnableFutureModeTest extends TestCase
{
    public function testFutureModeIsDisabledByDefault(): void
    {
        $client = $this->createClient();

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        self::assertFalse(FutureMode::isEnabled($api));
    }

    public function testEnableFutureModeEnablesFutureModeOnNewApis(): void
    {
        $client = $this->createClient();
        $client->enableFutureMode();

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        self::assertTrue(FutureMode::isEnabled($api));
    }

    public function testEnableFutureModeEnablesFutureModeOnExistingApis(): void
    {
        $client = $this->createClient();

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        $client->enableFutureMode();

        self::assertTrue(FutureMode::isEnabled($api));
        self::assertSame($api, $client->getApi('issue'));
    }

    public function testEnableFutureModeDoesNotEnableFutureModeOnOtherClients(): void
    {
        $client = $this->createClient();
        $otherClient = $this->createClient();

        $client->enableFutureMode();

        /** @var AbstractApi */
        $api = $otherClient->getApi('issue');

        self::assertFalse(FutureMode::isEnabled($api));
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

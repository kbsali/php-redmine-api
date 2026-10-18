<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\NativeCurlClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\NativeCurlClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(NativeCurlClient::class)]
final class EnableFutureModeTest extends TestCase
{
    public function testFutureModeIsDisabledByDefault(): void
    {
        $client = new NativeCurlClient('', '');

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        self::assertFalse(FutureMode::isEnabled($api));
    }

    public function testEnableFutureModeEnablesFutureModeOnNewApis(): void
    {
        $client = new NativeCurlClient('', '');
        $client->enableFutureMode();

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        self::assertTrue(FutureMode::isEnabled($api));
    }

    public function testEnableFutureModeEnablesFutureModeOnExistingApis(): void
    {
        $client = new NativeCurlClient('', '');

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        $client->enableFutureMode();

        self::assertTrue(FutureMode::isEnabled($api));
        self::assertSame($api, $client->getApi('issue'));
    }

    public function testEnableFutureModeDoesNotEnableFutureModeOnOtherClients(): void
    {
        $client = new NativeCurlClient('', '');
        $otherClient = new NativeCurlClient('', '');

        $client->enableFutureMode();

        /** @var AbstractApi */
        $api = $otherClient->getApi('issue');

        self::assertFalse(FutureMode::isEnabled($api));
    }
}

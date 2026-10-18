<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\NativeCurlClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Client\NativeCurlClient;
use Redmine\Future;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(NativeCurlClient::class)]
final class EnableFutureModeTest extends TestCase
{
    public function testEnableFutureModeEnablesFutureMode(): void
    {
        Future::disableForwardCompatibility();

        try {
            $client = new NativeCurlClient(
                '',
                '',
            );
            $client->enableFutureMode();

            self::assertTrue(Future::isForwardCompatibilityEnabled());
        } finally {
            Future::disableForwardCompatibility();
        }
    }

    public function testFutureModeIsDisabledByDefault(): void
    {
        $client = new NativeCurlClient('', '');

        /** @var AbstractApi */
        $api = $client->getApi('issue');

        self::assertFalse(FutureMode::isEnabled($api));
    }

    public function testEnableFutureModeEnablesFutureModeOnNewApis(): void
    {
        try {
            $client = new NativeCurlClient('', '');
            $client->enableFutureMode();

            /** @var AbstractApi */
            $api = $client->getApi('issue');

            self::assertTrue(FutureMode::isEnabled($api));
        } finally {
            Future::disableForwardCompatibility();
        }
    }

    public function testEnableFutureModeEnablesFutureModeOnExistingApis(): void
    {
        try {
            $client = new NativeCurlClient('', '');

            /** @var AbstractApi */
            $api = $client->getApi('issue');

            $client->enableFutureMode();

            self::assertTrue(FutureMode::isEnabled($api));
            self::assertSame($api, $client->getApi('issue'));
        } finally {
            Future::disableForwardCompatibility();
        }
    }

    public function testEnableFutureModeDoesNotEnableFutureModeOnOtherClients(): void
    {
        try {
            $client = new NativeCurlClient('', '');
            $otherClient = new NativeCurlClient('', '');

            $client->enableFutureMode();

            /** @var AbstractApi */
            $api = $otherClient->getApi('issue');

            self::assertFalse(FutureMode::isEnabled($api));
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

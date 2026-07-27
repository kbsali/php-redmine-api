<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\NativeCurlClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;
use Redmine\Future;

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
}

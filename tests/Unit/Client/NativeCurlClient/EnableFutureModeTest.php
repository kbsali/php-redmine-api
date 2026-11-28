<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Client\NativeCurlClient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Client\NativeCurlClient;

#[CoversClass(NativeCurlClient::class)]
final class EnableFutureModeTest extends TestCase
{
    public function testEnableFutureModeDoesNothing(): void
    {
        $client = new NativeCurlClient('', '');

        $api = $client->getApi('issue');

        $client->enableFutureMode();

        $this->assertSame($api, $client->getApi('issue'));
    }
}

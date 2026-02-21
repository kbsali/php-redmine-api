<?php

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Http\HttpClient;

#[CoversClass(Version::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Version::fromHttpClient($httpClient);

        $this->assertInstanceOf(Version::class, $api);
    }
}

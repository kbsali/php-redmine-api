<?php

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Http\HttpClient;

#[CoversClass(Membership::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Membership::fromHttpClient($httpClient);

        $this->assertInstanceOf(Membership::class, $api);
    }
}

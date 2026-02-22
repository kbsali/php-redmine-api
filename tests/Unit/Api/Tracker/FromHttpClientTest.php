<?php

namespace Redmine\Tests\Unit\Api\Tracker;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Tracker;
use Redmine\Http\HttpClient;

#[CoversClass(Tracker::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Tracker::fromHttpClient($httpClient);

        $this->assertInstanceOf(Tracker::class, $api);
    }
}

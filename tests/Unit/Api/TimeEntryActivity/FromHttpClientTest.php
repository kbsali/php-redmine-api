<?php

namespace Redmine\Tests\Unit\Api\TimeEntryActivity;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntryActivity;
use Redmine\Http\HttpClient;

#[CoversClass(TimeEntryActivity::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = TimeEntryActivity::fromHttpClient($httpClient);

        $this->assertInstanceOf(TimeEntryActivity::class, $api);
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\TimeEntry;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\TimeEntry;
use Redmine\Http\HttpClient;

#[CoversClass(TimeEntry::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = TimeEntry::fromHttpClient($httpClient);

        $this->assertInstanceOf(TimeEntry::class, $api);
    }
}

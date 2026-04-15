<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssuePriority;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssuePriority;
use Redmine\Http\HttpClient;

#[CoversClass(IssuePriority::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = IssuePriority::fromHttpClient($httpClient);

        $this->assertInstanceOf(IssuePriority::class, $api);
    }
}

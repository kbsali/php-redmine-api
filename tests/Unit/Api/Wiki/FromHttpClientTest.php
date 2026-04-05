<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Http\HttpClient;

#[CoversClass(Wiki::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Wiki::fromHttpClient($httpClient);

        $this->assertInstanceOf(Wiki::class, $api);
    }
}

<?php

namespace Redmine\Tests\Unit\Api\News;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\News;
use Redmine\Http\HttpClient;

#[CoversClass(News::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = News::fromHttpClient($httpClient);

        $this->assertInstanceOf(News::class, $api);
    }
}

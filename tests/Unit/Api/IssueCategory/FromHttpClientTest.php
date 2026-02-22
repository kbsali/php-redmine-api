<?php

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Http\HttpClient;

#[CoversClass(IssueCategory::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = IssueCategory::fromHttpClient($httpClient);

        $this->assertInstanceOf(IssueCategory::class, $api);
    }
}

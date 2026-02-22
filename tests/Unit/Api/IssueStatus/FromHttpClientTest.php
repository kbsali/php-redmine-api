<?php

namespace Redmine\Tests\Unit\Api\IssueStatus;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueStatus;
use Redmine\Http\HttpClient;

#[CoversClass(IssueStatus::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = IssueStatus::fromHttpClient($httpClient);

        $this->assertInstanceOf(IssueStatus::class, $api);
    }
}

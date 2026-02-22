<?php

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Http\HttpClient;

#[CoversClass(IssueRelation::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = IssueRelation::fromHttpClient($httpClient);

        $this->assertInstanceOf(IssueRelation::class, $api);
    }
}

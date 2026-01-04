<?php

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Http\HttpClient;

#[CoversClass(Group::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Group::fromHttpClient($httpClient);

        $this->assertInstanceOf(Group::class, $api);
    }
}

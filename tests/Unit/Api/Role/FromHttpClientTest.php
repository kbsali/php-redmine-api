<?php

namespace Redmine\Tests\Unit\Api\Role;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Role;
use Redmine\Http\HttpClient;

#[CoversClass(Role::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Role::fromHttpClient($httpClient);

        $this->assertInstanceOf(Role::class, $api);
    }
}

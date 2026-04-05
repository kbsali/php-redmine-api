<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Http\HttpClient;

#[CoversClass(User::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = User::fromHttpClient($httpClient);

        $this->assertInstanceOf(User::class, $api);
    }
}

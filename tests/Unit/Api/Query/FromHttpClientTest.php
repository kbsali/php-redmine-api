<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Query;
use Redmine\Http\HttpClient;

#[CoversClass(Query::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Query::fromHttpClient($httpClient);

        $this->assertInstanceOf(Query::class, $api);
    }
}

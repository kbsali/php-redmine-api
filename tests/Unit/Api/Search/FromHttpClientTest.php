<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Search;
use Redmine\Http\HttpClient;

#[CoversClass(Search::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Search::fromHttpClient($httpClient);

        $this->assertInstanceOf(Search::class, $api);
    }
}

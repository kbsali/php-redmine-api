<?php

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Http\HttpClient;

#[CoversClass(Attachment::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Attachment::fromHttpClient($httpClient);

        $this->assertInstanceOf(Attachment::class, $api);
    }
}

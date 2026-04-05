<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\CustomField;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Http\HttpClient;

#[CoversClass(CustomField::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = CustomField::fromHttpClient($httpClient);

        $this->assertInstanceOf(CustomField::class, $api);
    }
}

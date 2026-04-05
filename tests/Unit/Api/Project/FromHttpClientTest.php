<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Http\HttpClient;

#[CoversClass(Project::class)]
class FromHttpClientTest extends TestCase
{
    public function testReturnsCorrectObject(): void
    {
        $httpClient = $this->createStub(HttpClient::class);

        $api = Project::fromHttpClient($httpClient);

        $this->assertInstanceOf(Project::class, $api);
    }
}

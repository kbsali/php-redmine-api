<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Issue::class)]
class RemoveWatcherTest extends TestCase
{
    /**
     * @dataProvider getRemoveWatcherData
     */
    #[DataProvider('getRemoveWatcherData')]
    public function testRemoveWatcherReturnsCorrectResponse(int $issueId, int $watcherUserId, string $expectedPath, int $responseCode, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                $expectedPath,
                'application/xml',
                '',
                $responseCode,
                '',
                $response,
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($response, $api->removeWatcher($issueId, $watcherUserId));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getRemoveWatcherData(): array
    {
        return [
            'test with integers' => [
                25,
                5,
                '/issues/25/watchers/5.xml',
                204,
                '',
            ],
        ];
    }
}

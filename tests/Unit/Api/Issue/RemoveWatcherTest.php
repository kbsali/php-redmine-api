<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
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

    public function testRemoveWatcherWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/issues/25/watchers/5.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->removeWatcher(25, 5));
    }

    public function testRemoveWatcherWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/issues/25/watchers/5.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->removeWatcher(25, 5);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

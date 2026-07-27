<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Future;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(IssueCategory::class)]
class RemoveTest extends TestCase
{
    /**
     * @dataProvider getRemoveData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse(int $issueId, array $parameters, string $expectedPath, int $responseCode, string $response): void
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
        $api = IssueCategory::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($issueId, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getRemoveData(): array
    {
        return [
            'test without params' => [
                25,
                [],
                '/issue_categories/25.xml',
                204,
                '',
            ],
            'test with params' => [
                25,
                ['reassign_to_id' => 30],
                '/issue_categories/25.xml?reassign_to_id=30',
                204,
                '',
            ],
        ];
    }

    public function testRemoveWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/issue_categories/25.xml',
                'application/xml',
                '',
                500,
                '',
                'error body',
            ],
        );

        $api = IssueCategory::fromHttpClient($client);

        $this->assertSame('error body', $api->remove(25));
    }

    public function testRemoveWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/issue_categories/25.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = IssueCategory::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        try {
            Future::enableForwardCompatibility();

            $api->remove(25);
        } finally {
            Future::disableForwardCompatibility();
        }
    }
}

<?php

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
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
}

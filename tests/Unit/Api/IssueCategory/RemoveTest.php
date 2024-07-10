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
     */
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse($issueId, $params, $expectedPath, $responseCode, $response): void
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
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($issueId, $params));
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

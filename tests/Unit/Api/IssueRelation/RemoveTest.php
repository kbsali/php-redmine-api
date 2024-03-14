<?php

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(IssueRelation::class)]
class RemoveTest extends TestCase
{
    /**
     * @dataProvider getRemoveData
     */
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse($issueId, $expectedPath, $responseCode, $response)
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
                $response
            ]
        );

        // Create the object under test
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($issueId));
    }

    public static function getRemoveData(): array
    {
        return [
            'test with integers' => [
                25,
                '/relations/25.xml',
                204,
                '',
            ],
        ];
    }
}

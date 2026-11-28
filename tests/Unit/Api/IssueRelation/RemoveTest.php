<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(IssueRelation::class)]
final class RemoveTest extends TestCase
{
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse(int $issueId, string $expectedPath, int $responseCode, string $response): void
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
        $api = IssueRelation::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($issueId));
    }

    /**
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getRemoveData(): \Iterator
    {
        yield 'test with integers' => [
            25,
            '/relations/25.xml',
            204,
            '',
        ];
    }

    public function testRemoveWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'DELETE',
                '/relations/25.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = IssueRelation::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);


        $api->remove(25);
    }
}

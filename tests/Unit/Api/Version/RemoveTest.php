<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Version::class)]
final class RemoveTest extends TestCase
{
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse(int|string $id, string $expectedPath, int $responseCode, string $response): void
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
        $api = Version::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($id));
    }

    /**
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getRemoveData(): \Iterator
    {
        yield 'test with integers' => [
            5,
            '/versions/5.xml',
            204,
            '',
        ];
        yield 'test with string' => [
            '5',
            '/versions/5.xml',
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
                '/versions/5.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = Version::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);


        $api->remove(5);
    }
}

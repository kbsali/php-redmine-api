<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(User::class)]
final class RemoveTest extends TestCase
{
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse(int $id, string $expectedPath, int $responseCode, string $response): void
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
        $api = User::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($id));
    }

    /**
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getRemoveData(): \Iterator
    {
        yield 'test with integer' => [
            5,
            '/users/5.xml',
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
                '/users/5.xml',
                'application/xml',
                '',
                500,
                '',
                '',
            ],
        );

        $api = User::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);


        $api->remove(5);
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Version::class)]
class RemoveTest extends TestCase
{
    /**
     * @dataProvider getRemoveData
     *
     * @param mixed $id
     */
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse($id, string $expectedPath, int $responseCode, string $response): void
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
     * @return array<array<mixed>>
     */
    public static function getRemoveData(): array
    {
        return [
            'test with integers' => [
                5,
                '/versions/5.xml',
                204,
                '',
            ],
            'test with string' => [
                '5',
                '/versions/5.xml',
                204,
                '',
            ],
        ];
    }
}

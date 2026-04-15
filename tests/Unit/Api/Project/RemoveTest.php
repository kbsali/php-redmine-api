<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
class RemoveTest extends TestCase
{
    /**
     * @dataProvider getRemoveData
     */
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
        $api = Project::fromHttpClient($client);

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
                '/projects/5.xml',
                204,
                '',
            ],
        ];
    }
}

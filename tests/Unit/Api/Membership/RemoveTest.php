<?php

namespace Redmine\Tests\Unit\Api\Membership;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Membership;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Membership::class)]
class RemoveTest extends TestCase
{
    /**
     * @dataProvider getRemoveData
     */
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse($id, $expectedPath, $responseCode, $response): void
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
        $api = new Membership($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($id));
    }

    public static function getRemoveData(): array
    {
        return [
            'test with integers' => [
                25,
                '/memberships/25.xml',
                204,
                '',
            ],
        ];
    }
}

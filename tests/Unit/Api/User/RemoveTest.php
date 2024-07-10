<?php

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(User::class)]
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
        $api = new User($client);

        // Perform the tests
        $this->assertSame($response, $api->remove($id));
    }

    public static function getRemoveData(): array
    {
        return [
            'test with integer' => [
                5,
                '/users/5.xml',
                204,
                '',
            ],
        ];
    }
}

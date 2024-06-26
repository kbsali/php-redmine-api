<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Wiki;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Wiki;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Wiki::class)]
class RemoveTest extends TestCase
{
    /**
     * @dataProvider getRemoveData
     */
    #[DataProvider('getRemoveData')]
    public function testRemoveReturnsCorrectResponse($id, $page, $expectedPath, $responseCode, $response)
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
            ]
        );

        // Create the object under test
        $api = new Wiki($client);

        // Perform the tests
        $this->assertSame('', $api->remove($id, $page));
    }

    public static function getRemoveData(): array
    {
        return [
            'test with integer' => [
                5,
                'test',
                '/projects/5/wiki/test.xml',
                204,
                '',
            ],
            'test with special chars in page name' => [
                5,
                'test page',
                '/projects/5/wiki/test+page.xml',
                204,
                '',
            ],
        ];
    }
}

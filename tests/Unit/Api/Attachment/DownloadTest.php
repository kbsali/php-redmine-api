<?php

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Attachment::class)]
class DownloadTest extends TestCase
{
    /**
     * @dataProvider getDownloadData
     * @param mixed $id
     * @param mixed $expectedReturn
     */
    #[DataProvider('getDownloadData')]
    public function testDownloadReturnsCorrectResponse($id, string $expectedPath, int $responseCode, string $response, $expectedReturn): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                '',
                '',
                $responseCode,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Attachment::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->download($id));
    }

    public static function getDownloadData(): array
    {
        return [
            'string response with integer id' => [5, '/attachments/download/5', 200, 'attachment-content', 'attachment-content'],
            'string response with string id' => ['5', '/attachments/download/5', 200, 'attachment-content', 'attachment-content'],
            'false response' => [5, '/attachments/download/5', 404, '', false],
        ];
    }
}

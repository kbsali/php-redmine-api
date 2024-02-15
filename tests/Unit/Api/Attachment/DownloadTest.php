<?php

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Attachment::download
 */
class DownloadTest extends TestCase
{
    /**
     * @dataProvider getDownloadData
     */
    public function testDownloadReturnsCorrectResponse($id, $expectedPath, $response, $expectedReturn)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                '',
                '',
                200,
                'application/json',
                $response
            ]
        );

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->download($id));
    }

    public static function getDownloadData(): array
    {
        return [
            'string response with integer id' => [5, '/attachments/download/5', 'attachment-content', 'attachment-content'],
            'string response with string id' => ['5', '/attachments/download/5', 'attachment-content', 'attachment-content'],
            'false response' => [5, '/attachments/download/5', '', false],
        ];
    }
}

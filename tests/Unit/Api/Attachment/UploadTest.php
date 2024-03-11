<?php

namespace Redmine\Tests\Unit\Api\Attachment;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Attachment;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Attachment::upload
 */
class UploadTest extends TestCase
{
    /**
     * @dataProvider getUploadData
     */
    #[DataProvider('getUploadData')]
    public function testUploadReturnsCorrectResponse($attachment, $params, $expectedAttachment, $expectedPath, $responseCode, $response, $expectedReturn)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                $expectedPath,
                'application/octet-stream',
                $expectedAttachment,
                $responseCode,
                'application/json',
                $response
            ]
        );

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->upload($attachment, $params));
    }

    public static function getUploadData(): array
    {
        return [
            'test attachment without params' => [
                'attachment-content',
                [],
                'attachment-content',
                '/uploads.json',
                201,
                '{}',
                '{}',
            ],
            'test attachment returns empty string' => [
                'attachment-content',
                [],
                'attachment-content',
                '/uploads.json',
                201,
                '',
                '',
            ],
            'test attachment with params' => [
                'attachment-content',
                [
                    'filename' => 'testfile.txt'
                ],
                'attachment-content',
                '/uploads.json?filename=testfile.txt',
                201,
                '{"upload":{}}',
                '{"upload":{}}',
            ],
            'test attachment with filepath' => [
                '/path/to/testfile_01.txt',
                [
                    'filename' => 'testfile.txt'
                ],
                '/path/to/testfile_01.txt',
                '/uploads.json?filename=testfile.txt',
                201,
                '{"upload":{}}',
                '{"upload":{}}',
            ],
        ];
    }
}

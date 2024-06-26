<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Issue::class)]
class AttachManyTest extends TestCase
{
    /**
     * @dataProvider getAttachManyData
     */
    #[DataProvider('getAttachManyData')]
    public function testAttachManyReturnsCorrectResponse($issueId, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/json',
                $expectedBody,
                $responseCode,
                '',
                $response,
            ]
        );

        // AttachMany the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame('', $api->attachMany($issueId, $parameters));
    }

    public static function getAttachManyData(): array
    {
        return [
            'test without parameters' => [
                5,
                [],
                '/issues/5.json',
                <<<JSON
                {
                    "issue": {
                        "id": 5,
                        "uploads": []
                    }
                }
                JSON,
                201,
                '',
            ],
            'test with many attachments' => [
                5,
                [
                    [
                        'token' => '1.sample-test-token',
                        'filename' => 'test-1.txt',
                    ],
                    [
                        'token' => '2.sample-test-token',
                        'filename' => 'test-2.txt',
                    ],
                ],
                '/issues/5.json',
                <<<JSON
                {
                    "issue": {
                        "id": 5,
                        "uploads": [
                            {
                                "filename": "test-1.txt",
                                "token": "1.sample-test-token"
                            },
                            {
                                "filename": "test-2.txt",
                                "token": "2.sample-test-token"
                            }
                        ]
                    }
                }
                JSON,
                201,
                '',
            ],
        ];
    }
}

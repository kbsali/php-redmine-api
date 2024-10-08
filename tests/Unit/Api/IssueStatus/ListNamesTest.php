<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueStatus;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueStatus;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(IssueStatus::class)]
class ListNamesTest extends TestCase
{
    /**
     * @dataProvider getListNamesData
     */
    #[DataProvider('getListNamesData')]
    public function testListNamesReturnsCorrectResponse($expectedPath, $responseCode, $response, $expectedResponse): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                'application/json',
                '',
                $responseCode,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = new IssueStatus($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    public static function getListNamesData(): array
    {
        return [
            'test without issue statuses' => [
                '/issue_statuses.json',
                201,
                <<<JSON
                {
                    "issue_statuses": []
                }
                JSON,
                [],
            ],
            'test with multiple issue statuses' => [
                '/issue_statuses.json',
                201,
                <<<JSON
                {
                    "issue_statuses": [
                        {"id": 7, "name": "IssueStatus C"},
                        {"id": 8, "name": "IssueStatus B"},
                        {"id": 9, "name": "IssueStatus A"}
                    ]
                }
                JSON,
                [
                    7 => "IssueStatus C",
                    8 => "IssueStatus B",
                    9 => "IssueStatus A",
                ],
            ],
        ];
    }

    public function testListNamesCallsHttpClientOnlyOnce(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issue_statuses.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "issue_statuses": [
                        {
                            "id": 1,
                            "name": "IssueStatus 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new IssueStatus($client);

        // Perform the tests
        $this->assertSame([1 => 'IssueStatus 1'], $api->listNames());
        $this->assertSame([1 => 'IssueStatus 1'], $api->listNames());
        $this->assertSame([1 => 'IssueStatus 1'], $api->listNames());
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Group::class)]
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
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    public static function getListNamesData(): array
    {
        return [
            'test without groups' => [
                '/groups.json',
                201,
                <<<JSON
                {
                    "groups": []
                }
                JSON,
                [],
            ],
            'test with multiple groups' => [
                '/groups.json',
                201,
                <<<JSON
                {
                    "groups": [
                        {"id": 7, "name": "Group 3"},
                        {"id": 8, "name": "Group 2"},
                        {"id": 9, "name": "Group 1"}
                    ]
                }
                JSON,
                [
                    7 => "Group 3",
                    8 => "Group 2",
                    9 => "Group 1",
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
                '/groups.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "groups": [
                        {
                            "id": 1,
                            "name": "Group 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new Group($client);

        // Perform the tests
        $this->assertSame([1 => 'Group 1'], $api->listNames());
        $this->assertSame([1 => 'Group 1'], $api->listNames());
        $this->assertSame([1 => 'Group 1'], $api->listNames());
    }
}

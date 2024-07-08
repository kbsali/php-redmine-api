<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(User::class)]
class ListLoginsTest extends TestCase
{
    /**
     * @dataProvider getListLoginsData
     */
    #[DataProvider('getListLoginsData')]
    public function testListLoginsReturnsCorrectResponse($expectedPath, $responseCode, $response, $expectedResponse)
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
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listLogins());
    }

    public static function getListLoginsData(): array
    {
        return [
            'test without users' => [
                '/users.json?limit=100&offset=0',
                201,
                <<<JSON
                {
                    "users": []
                }
                JSON,
                [],
            ],
            'test with multiple users' => [
                '/users.json?limit=100&offset=0',
                201,
                <<<JSON
                {
                    "users": [
                        {"id": 7, "login": "username_C"},
                        {"id": 8, "login": "username_B"},
                        {"id": 9, "login": "username_A"}
                    ]
                }
                JSON,
                [
                    7 => "username_C",
                    8 => "username_B",
                    9 => "username_A",
                ],
            ],
        ];
    }

    public function testListLoginsWithALotOfUsersHandlesPagination()
    {
        $assertData = [];
        $usersRequest1 = [];
        $usersRequest2 = [];
        $usersRequest3 = [];

        for ($i = 1; $i <= 100; $i++) {
            $login = 'user_' . $i;

            $assertData[$i] = $login;
            $usersRequest1[] = ['id' => $i, 'login' => $login];
        }

        for ($i = 101; $i <= 200; $i++) {
            $login = 'user_' . $i;

            $assertData[$i] = $login;
            $usersRequest2[] = ['id' => $i, 'login' => $login];
        }

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/users.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['users' => $usersRequest1]),
            ],
            [
                'GET',
                '/users.json?limit=100&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['users' => $usersRequest2]),
            ],
            [
                'GET',
                '/users.json?limit=100&offset=200',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['users' => $usersRequest3]),
            ],
        );

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($assertData, $api->listLogins());
    }

    public function testListLoginsCallsHttpClientOnlyOnce()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/users.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "users": [
                        {
                            "id": 1,
                            "login": "username"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame([1 => 'username'], $api->listLogins());
        $this->assertSame([1 => 'username'], $api->listLogins());
        $this->assertSame([1 => 'username'], $api->listLogins());
    }
}

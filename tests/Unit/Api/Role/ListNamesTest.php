<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Role;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Role;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Role::class)]
class ListNamesTest extends TestCase
{
    /**
     * @dataProvider getListNamesData
     */
    #[DataProvider('getListNamesData')]
    public function testListNamesReturnsCorrectResponse($expectedPath, $responseCode, $response, $expectedResponse)
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
        $api = new Role($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    public static function getListNamesData(): array
    {
        return [
            'test without roles' => [
                '/roles.json',
                201,
                <<<JSON
                {
                    "roles": []
                }
                JSON,
                [],
            ],
            'test with multiple roles' => [
                '/roles.json',
                201,
                <<<JSON
                {
                    "roles": [
                        {"id": 7, "name": "Role 3"},
                        {"id": 8, "name": "Role 2"},
                        {"id": 9, "name": "Role 1"}
                    ]
                }
                JSON,
                [
                    7 => "Role 3",
                    8 => "Role 2",
                    9 => "Role 1",
                ],
            ],
        ];
    }

    public function testListNamesCallsHttpClientOnlyOnce()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/roles.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "roles": [
                        {
                            "id": 1,
                            "name": "Role 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new Role($client);

        // Perform the tests
        $this->assertSame([1 => 'Role 1'], $api->listNames());
        $this->assertSame([1 => 'Role 1'], $api->listNames());
        $this->assertSame([1 => 'Role 1'], $api->listNames());
    }
}

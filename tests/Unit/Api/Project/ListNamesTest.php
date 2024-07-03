<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
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
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    public static function getListNamesData(): array
    {
        return [
            'test without projects' => [
                '/projects.json',
                201,
                <<<JSON
                {
                    "projects": []
                }
                JSON,
                [],
            ],
            'test with multiple projects' => [
                '/projects.json',
                201,
                <<<JSON
                {
                    "projects": [
                        {"id": 7, "name": "Project C"},
                        {"id": 8, "name": "Project B"},
                        {"id": 9, "name": "Project A"}
                    ]
                }
                JSON,
                [
                    7 => "Project C",
                    8 => "Project B",
                    9 => "Project A",
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
                '/projects.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "projects": [
                        {
                            "id": 1,
                            "name": "Project 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame([1 => 'Project 1'], $api->listNames());
        $this->assertSame([1 => 'Project 1'], $api->listNames());
        $this->assertSame([1 => 'Project 1'], $api->listNames());
    }
}

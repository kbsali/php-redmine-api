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
                '/projects.json?limit=100&offset=0',
                201,
                <<<JSON
                {
                    "projects": []
                }
                JSON,
                [],
            ],
            'test with multiple projects' => [
                '/projects.json?limit=100&offset=0',
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

    public function testListNamesWithALotOfProjectsHandlesPagination()
    {
        $assertData = [];
        $projectsRequest1 = [];
        $projectsRequest2 = [];
        $projectsRequest3 = [];

        for ($i = 1; $i <= 100; $i++) {
            $name = 'Project ' . $i;

            $assertData[$i] = $name;
            $projectsRequest1[] = ['id' => $i, 'name' => $name];
        }

        for ($i = 101; $i <= 200; $i++) {
            $name = 'Project ' . $i;

            $assertData[$i] = $name;
            $projectsRequest2[] = ['id' => $i, 'name' => $name];
        }

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['projects' => $projectsRequest1]),
            ],
            [
                'GET',
                '/projects.json?limit=100&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['projects' => $projectsRequest2]),
            ],
            [
                'GET',
                '/projects.json?limit=100&offset=200',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['projects' => $projectsRequest3]),
            ],
        );

        // Create the object under test
        $api = new Project($client);

        // Perform the tests
        $this->assertSame($assertData, $api->listNames());
    }

    public function testListNamesCallsHttpClientOnlyOnce()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json?limit=100&offset=0',
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

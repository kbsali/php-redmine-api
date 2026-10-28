<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Project;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Project;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Project::class)]
class ListNamesTest extends TestCase
{
    /**
     * @dataProvider getListNamesData
     *
     * @param array<mixed> $expectedResponse
     */
    #[DataProvider('getListNamesData')]
    public function testListNamesReturnsCorrectResponse(string $expectedPath, int $responseCode, string $response, array $expectedResponse): void
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
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNames());
    }

    /**
     * @return array<array<mixed>>
     */
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

    public function testListNamesWithALotOfProjectsHandlesPagination(): void
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
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($assertData, $api->listNames());
    }

    public function testListNamesCallsHttpClientOnlyOnce(): void
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
        $api = Project::fromHttpClient($client);

        // Perform the tests
        $firstResult = $api->listNames();
        $secondResult = $api->listNames();
        $thirdResult = $api->listNames();

        $this->assertSame([1 => 'Project 1'], $firstResult);
        $this->assertSame([1 => 'Project 1'], $secondResult);
        $this->assertSame([1 => 'Project 1'], $thirdResult);
    }

    public function testListNamesAfterUnexpectedResponseOnSecondPageRequestsAllPagesAgain(): void
    {
        $assertData = [];
        $request1 = [];

        for ($i = 1; $i <= 100; $i++) {
            $assertData[$i] = 'Project ' . $i;
            $request1[] = ['id' => $i, 'name' => 'Project ' . $i];
        }

        $assertData[101] = 'Project 101';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['projects' => $request1]),
            ],
            [
                'GET',
                '/projects.json?limit=100&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
            [
                'GET',
                '/projects.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                json_encode(['projects' => $request1]),
            ],
            [
                'GET',
                '/projects.json?limit=100&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                '{"projects":[{"id":101,"name":"Project 101"}]}',
            ],
        );

        // Create the object under test
        $api = Project::fromHttpClient($client);

        // Perform the tests
        try {
            $api->listNames();
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame('', $e->getResponse()->getContent());
        }

        $this->assertSame($assertData, $api->listNames());
    }
}

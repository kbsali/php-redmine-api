<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Exception\InvalidParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use stdClass;

#[CoversClass(IssueCategory::class)]
class ListNamesByProjectTest extends TestCase
{
    /**
     * @dataProvider getListNamesByProjectData
     */
    #[DataProvider('getListNamesByProjectData')]
    public function testListNamesByProjectReturnsCorrectResponse($projectIdentifier, $expectedPath, $responseCode, $response, $expectedResponse)
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
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNamesByProject($projectIdentifier));
    }

    public static function getListNamesByProjectData(): array
    {
        return [
            'test without issue categories' => [
                5,
                '/projects/5/issue_categories.json',
                201,
                <<<JSON
                {
                    "issue_categories": []
                }
                JSON,
                [],
            ],
            'test with multiple categories' => [
                'test-project',
                '/projects/test-project/issue_categories.json',
                201,
                <<<JSON
                {
                    "issue_categories": [
                        {"id": 7, "name": "IssueCategory 3"},
                        {"id": 8, "name": "IssueCategory 2"},
                        {"id": 9, "name": "IssueCategory 1"}
                    ]
                }
                JSON,
                [
                    7 => "IssueCategory 3",
                    8 => "IssueCategory 2",
                    9 => "IssueCategory 1",
                ],
            ],
        ];
    }

    public function testListNamesByProjectCallsHttpClientOnlyOnce()
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/projects/5/issue_categories.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "issue_categories": [
                        {
                            "id": 1,
                            "name": "IssueCategory 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame([1 => 'IssueCategory 1'], $api->listNamesByProject(5));
        $this->assertSame([1 => 'IssueCategory 1'], $api->listNamesByProject(5));
        $this->assertSame([1 => 'IssueCategory 1'], $api->listNamesByProject(5));
    }

    /**
     * @dataProvider getInvalidProjectIdentifiers
     */
    #[DataProvider('getInvalidProjectIdentifiers')]
    public function testListNamesByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier)
    {
        $api = new IssueCategory($this->createMock(HttpClient::class));

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\IssueCategory::listNamesByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listNamesByProject($projectIdentifier);
    }

    public static function getInvalidProjectIdentifiers(): array
    {
        return [
            'null' => [null],
            'true' => [true],
            'false' => [false],
            'float' => [0.0],
            'array' => [[]],
            'object' => [new stdClass()],
        ];
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Version;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Version;
use Redmine\Exception\InvalidParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\TestDataProvider;

#[CoversClass(Version::class)]
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
        $api = new Version($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->listNamesByProject($projectIdentifier));
    }

    public static function getListNamesByProjectData(): array
    {
        return [
            'test without versions' => [
                5,
                '/projects/5/versions.json',
                201,
                <<<JSON
                {
                    "versions": []
                }
                JSON,
                [],
            ],
            'test with multiple categories' => [
                'test-project',
                '/projects/test-project/versions.json',
                201,
                <<<JSON
                {
                    "versions": [
                        {"id": 7, "name": "Version 3"},
                        {"id": 8, "name": "Version 2"},
                        {"id": 9, "name": "Version 1"}
                    ]
                }
                JSON,
                [
                    7 => "Version 3",
                    8 => "Version 2",
                    9 => "Version 1",
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
                '/projects/5/versions.json',
                'application/json',
                '',
                200,
                'application/json',
                <<<JSON
                {
                    "versions": [
                        {
                            "id": 1,
                            "name": "Version 1"
                        }
                    ]
                }
                JSON,
            ],
        );

        // Create the object under test
        $api = new Version($client);

        // Perform the tests
        $this->assertSame([1 => 'Version 1'], $api->listNamesByProject(5));
        $this->assertSame([1 => 'Version 1'], $api->listNamesByProject(5));
        $this->assertSame([1 => 'Version 1'], $api->listNamesByProject(5));
    }

    /**
     * @dataProvider Redmine\Tests\Fixtures\TestDataProvider::getInvalidProjectIdentifiers
     */
    #[DataProviderExternal(TestDataProvider::class, 'getInvalidProjectIdentifiers')]
    public function testListNamesByProjectWithWrongProjectIdentifierThrowsException($projectIdentifier)
    {
        $api = new Version($this->createMock(HttpClient::class));

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Redmine\Api\Version::listNamesByProject(): Argument #1 ($projectIdentifier) must be of type int or string');

        $api->listNamesByProject($projectIdentifier);
    }
}

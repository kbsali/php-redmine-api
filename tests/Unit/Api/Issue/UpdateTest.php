<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Api\Param;
use Redmine\Exception\InvalidParameterException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(Issue::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                '',
                $response,
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    /**
     * @dataProvider getUpdateData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateWithFutureModeReturnsCorrectResponse(int $id, array $parameters, string $expectedPath, string $expectedBody, int $responseCode, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                '',
                $response,
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getUpdateData(): array
    {
        return [
            'test with title' => [
                1,
                ['name' => 'Issue title'],
                '/issues/1.xml',
                '<?xml version="1.0"?><issue><id>1</id><name>Issue title</name></issue>',
                204,
                '',
            ],
            'test with all parameters' => [
                1,
                [
                    'subject' => 'test note (xml) 1',
                    'notes' => 'test note api',
                    'assigned_to_id' => 1,
                    'status_id' => 2,
                    'priority_id' => 5,
                    'due_date' => '2014-05-13',
                    // not testable because this will trigger a status name to id resolving
                    // 'status' => 'Resolved',
                ],
                '/issues/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <subject>test note (xml) 1</subject>
                    <notes>test note api</notes>
                    <assigned_to_id>1</assigned_to_id>
                    <status_id>2</status_id>
                    <priority_id>5</priority_id>
                    <due_date>2014-05-13</due_date>
                </issue>
                XML,
                204,
                '',
            ],
            'test assign user to issue' => [
                1,
                [
                    'assigned_to_id' => 5,
                ],
                '/issues/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <assigned_to_id>5</assigned_to_id>
                </issue>
                XML,
                204,
                '',
            ],
            'test assign fixed_version_id to issue' => [
                1,
                [
                    'fixed_version_id' => 5,
                ],
                '/issues/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <fixed_version_id>5</fixed_version_id>
                </issue>
                XML,
                204,
                '',
            ],
            'test unassign fixed_version_id from issue' => [
                1,
                [
                    'fixed_version_id' => '',
                ],
                '/issues/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <fixed_version_id></fixed_version_id>
                </issue>
                XML,
                204,
                '',
            ],
            'test clear description of issue' => [
                1,
                [
                    'description' => Param::Clear,
                ],
                '/issues/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <description></description>
                </issue>
                XML,
                204,
                '',
            ],
        ];
    }

    public function testUpdateCleansParameters(): void
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
                '{"projects":[{"name":"Project Name","id":3}]}',
            ],
            [
                'GET',
                '/projects/3/issue_categories.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"issue_categories":[{"name":"Category Name","id":45}]}',
            ],
            [
                'GET',
                '/issue_statuses.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"issue_statuses":[{"name":"Status Name","id":123}]}',
            ],
            [
                'GET',
                '/trackers.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"trackers":[{"name":"Tracker Name","id":9}]}',
            ],
            [
                'GET',
                '/users.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                '{"users":[{"login":"Author Name","id":5},{"login":"Assigned to User Name","id":6}]}',
            ],
            [
                'PUT',
                '/issues/70.xml',
                'application/xml',
                <<<XML
                <?xml version="1.0"?>
                <issue>
                    <id>70</id>
                    <project_id>3</project_id>
                    <category_id>45</category_id>
                    <status_id>123</status_id>
                    <tracker_id>9</tracker_id>
                    <assigned_to_id>6</assigned_to_id>
                    <author_id>5</author_id>
                </issue>
                XML,
                204,
                '',
                '',
            ],
        );

        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        ];

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $this->assertSame('', $api->update(70, $parameters));
    }

    public function testUpdateUnassignUserWithoutFutureModeSendsXml(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <assigned_to_id></assigned_to_id>
                </issue>
                XML,
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['assigned_to_id' => '']));
    }

    /**
     * @dataProvider getUpdateWithJsonData
     *
     * @param array<mixed> $parameters
     */
    #[DataProvider('getUpdateWithJsonData')]
    public function testUpdateUnassignUserWithFutureModeSendsJson(array $parameters, string $expectedBody): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.json',
                'application/json',
                $expectedBody,
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(1, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getUpdateWithJsonData(): array
    {
        return [
            'test unassign user from issue' => [
                ['assigned_to_id' => ''],
                '{"issue":{"id":1,"assigned_to_id":""}}',
            ],
            'test unassign user and update other fields' => [
                ['subject' => 'new subject', 'notes' => 'unassigned', 'assigned_to_id' => '', 'fixed_version_id' => ''],
                '{"issue":{"id":1,"subject":"new subject","notes":"unassigned","assigned_to_id":"","fixed_version_id":""}}',
            ],
            'test unassign user and update custom fields' => [
                ['assigned_to_id' => '', 'custom_fields' => [['id' => 1, 'value' => 'Note']]],
                '{"issue":{"id":1,"assigned_to_id":"","custom_fields":[{"id":1,"value":"Note"}]}}',
            ],
        ];
    }

    public function testUpdateClearAssigneeWithParamClearSendsJsonWithoutFutureMode(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.json',
                'application/json',
                '{"issue":{"id":1,"assigned_to_id":""}}',
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['assigned_to_id' => Param::Clear]));
    }

    public function testUpdateClearAssigneeByLoginWithParamClearDoesNotLookUpTheUser(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.json',
                'application/json',
                '{"issue":{"id":1,"assigned_to_id":""}}',
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(1, ['assigned_to' => Param::Clear]));
    }

    public function testUpdateClearCategoryWithParamClearDoesNotLookUpTheCategory(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>1</id><category_id></category_id></issue>',
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['category' => Param::Clear]));
    }

    public function testUpdateConvertsNestedParamClear(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>1</id><custom_fields type="array"><custom_field id="1"><value></value></custom_field></custom_fields></issue>',
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['custom_fields' => [['id' => 1, 'value' => Param::Clear]]]));
    }

    public function testUpdateUnassignUserWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.json',
                'application/json',
                '{"issue":{"id":1,"assigned_to_id":""}}',
                422,
                'application/json',
                '{"errors":["Subject cannot be blank"]}',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        FutureMode::enable($api);

        $api->update(1, ['assigned_to_id' => '']);
    }

    public function testUpdateWithIncorrectStatusCodeReturnsBody(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>1</id></issue>',
                500,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(1, []));
    }

    public function testUpdateWithIncorrectStatusCodeThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>1</id></issue>',
                500,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);

        FutureMode::enable($api);

        $api->update(1, []);
    }

    /**
     * @return array<string,array{mixed}>
     */
    public static function getEmptyValues(): array
    {
        return [
            'empty string' => [''],
            'empty array' => [[]],
            'false' => [false],
        ];
    }

    #[DataProvider('getEmptyValues')]
    public function testUpdateWithFutureModeThrowsOnEmptyValue(mixed $value): void
    {
        $client = $this->createMock(HttpClient::class);
        $client->expects($this->never())->method('request');

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/"description".*Param::Clear/');

        $api->update(1, ['description' => $value]);
    }

    #[DataProvider('getEmptyValues')]
    public function testUpdateWithoutFutureModeDropsEmptyValue(mixed $value): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>1</id></issue>',
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(1, ['description' => $value, 'subject' => null]));
    }

    public function testUpdateWithFutureModeAllowsNullAndEmptyStringForUnassign(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'PUT',
                '/issues/1.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><id>1</id><fixed_version_id></fixed_version_id></issue>',
                204,
                '',
                '',
            ],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(1, ['description' => null, 'fixed_version_id' => '']));
    }

    /**
     * @dataProvider getUnknownNames
     *
     * @param array<mixed> $params
     */
    #[DataProvider('getUnknownNames')]
    public function testUpdateWithFutureModeThrowsOnUnknownName(array $params, string $lookupPath, string $lookupResponse, string $expectedMessage): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', $lookupPath, 'application/json', '', 200, 'application/json', $lookupResponse],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage($expectedMessage);

        $api->update(5, $params);
    }

    /**
     * @return array<string,array{array<mixed>,string,string,string}>
     */
    public static function getUnknownNames(): array
    {
        return [
            'project' => [
                ['project' => 'Missing'],
                '/projects.json?limit=100&offset=0',
                '{"projects":[{"name":"Project Name","id":3}]}',
                'The value "Missing" of the parameter "project" is unknown',
            ],
            'category' => [
                ['category' => 'Missing', 'project_id' => 1],
                '/projects/1/issue_categories.json',
                '{"issue_categories":[{"name":"Category Name","id":7}]}',
                'The value "Missing" of the parameter "category" is unknown',
            ],
            'status' => [
                ['status' => 'Missing'],
                '/issue_statuses.json',
                '{"issue_statuses":[{"name":"Status Name","id":123}]}',
                'The value "Missing" of the parameter "status" is unknown',
            ],
            'tracker' => [
                ['tracker' => 'Missing'],
                '/trackers.json',
                '{"trackers":[{"name":"Tracker Name","id":9}]}',
                'The value "Missing" of the parameter "tracker" is unknown',
            ],
            'assigned_to' => [
                ['assigned_to' => 'Missing'],
                '/users.json?limit=100&offset=0',
                '{"users":[{"login":"user_5","id":5}]}',
                'The value "Missing" of the parameter "assigned_to" is unknown',
            ],
            'author' => [
                ['author' => 'Missing'],
                '/users.json?limit=100&offset=0',
                '{"users":[{"login":"user_5","id":5}]}',
                'The value "Missing" of the parameter "author" is unknown',
            ],
        ];
    }

    public function testUpdateWithFutureModeResolvesCategoryWithProjectOfIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issues/5.json', 'application/json', '', 200, 'application/json', '{"issue":{"id":5,"project":{"id":3,"name":"Project Name"}}}'],
            ['GET', '/projects/3/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><category_id>7</category_id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(5, ['category' => 'Category Name']));
    }

    public function testUpdateWithFutureModeUsesProjectIdOfTheCallWithoutLoadingTheIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/projects/1/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><project_id>1</project_id><category_id>7</category_id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(5, ['project_id' => 1, 'category' => 'Category Name']));
    }

    public function testUpdateWithFutureModeUsesProjectNameOfTheCallWithoutLoadingTheIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/projects.json?limit=100&offset=0', 'application/json', '', 200, 'application/json', '{"projects":[{"name":"Project Name","id":3}]}'],
            ['GET', '/projects/3/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><project_id>3</project_id><category_id>7</category_id></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame('', $api->update(5, ['project' => 'Project Name', 'category' => 'Category Name']));
    }

    public function testUpdateWithFutureModeThrowsOnUnknownCategoryOfProjectOfIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issues/5.json', 'application/json', '', 200, 'application/json', '{"issue":{"id":5,"project":{"id":3,"name":"Project Name"}}}'],
            ['GET', '/projects/3/issue_categories.json', 'application/json', '', 200, 'application/json', '{"issue_categories":[{"name":"Category Name","id":7}]}'],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('The value "Missing" of the parameter "category" is unknown');

        $api->update(5, ['category' => 'Missing']);
    }

    public function testUpdateWithFutureModeThrowsIfTheIssueCannotBeLoaded(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['GET', '/issues/5.json', 'application/json', '', 404, 'application/json', '{"errors":["Not found"]}'],
        );

        $api = Issue::fromHttpClient($client);

        FutureMode::enable($api);

        $this->expectException(UnexpectedResponseException::class);

        $api->update(5, ['category' => 'Category Name']);
    }

    public function testUpdateWithoutFutureModeDoesNotLoadTheIssue(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            ['PUT', '/issues/5.xml', 'application/xml', '<?xml version="1.0"?><issue><id>5</id><category>Category Name</category></issue>', 204, '', ''],
        );

        $api = Issue::fromHttpClient($client);

        $this->assertSame('', $api->update(5, ['category' => 'Category Name']));
    }
}

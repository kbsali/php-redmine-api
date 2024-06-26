<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Issue::class)]
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
    #[DataProvider('getUpdateData')]
    public function testUpdateReturnsCorrectResponse($id, $parameters, $expectedPath, $expectedBody, $responseCode, $response)
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
            ]
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

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
                    <priority_id>5</priority_id>
                    <status_id>2</status_id>
                    <assigned_to_id>1</assigned_to_id>
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
            'test unassign user from issue' => [
                1,
                [
                    'assigned_to_id' => '',
                ],
                '/issues/1.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue>
                    <id>1</id>
                    <assigned_to_id></assigned_to_id>
                </issue>
                XML,
                204,
                '',
            ],
        ];
    }

    public function testUpdateCleansParameters()
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
                '/users.json',
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
                    <category_id>45</category_id>
                    <status_id>123</status_id>
                    <tracker_id>9</tracker_id>
                    <assigned_to_id>6</assigned_to_id>
                    <project_id>3</project_id>
                    <author_id>5</author_id>
                </issue>
                XML,
                204,
                '',
                '',
            ]
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
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame('', $api->update(70, $parameters));
    }
}

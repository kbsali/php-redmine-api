<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

#[CoversClass(Issue::class)]
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    #[DataProvider('getCreateData')]
    public function testCreateReturnsCorrectResponse($parameters, $expectedPath, $expectedBody, $responseCode, $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                $expectedPath,
                'application/xml',
                $expectedBody,
                $responseCode,
                'application/xml',
                $response,
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $return = $api->create($parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $return);
        $this->assertXmlStringEqualsXmlString($response, $return->asXml());
    }

    public static function getCreateData(): array
    {
        return [
            'test without parameters' => [
                [],
                '/issues.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issue/>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
            'test with minimal parameters' => [
                [
                    'subject' => 'Issue subject',
                    'project_id' => 1,
                    'tracker_id' => 2,
                    'priority_id' => 3,
                    'status_id' => 4,
                ],
                '/issues.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issue>
                    <subject>Issue subject</subject>
                    <project_id>1</project_id>
                    <priority_id>3</priority_id>
                    <status_id>4</status_id>
                    <tracker_id>2</tracker_id>
                </issue>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
            'test with line break in description' => [
                [
                    'subject' => 'Issue subject',
                    'description' => "line1\nline2",
                    'project_id' => 1,
                    'tracker_id' => 2,
                    'priority_id' => 3,
                    'status_id' => 4,
                ],
                '/issues.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issue>
                    <subject>Issue subject</subject>
                    <description>line1
                line2</description>
                    <project_id>1</project_id>
                    <priority_id>3</priority_id>
                    <status_id>4</status_id>
                    <tracker_id>2</tracker_id>
                </issue>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
            'test with with some xml entities' => [
                [
                    'subject' => 'Issue subject with some xml entities: & < > " \' ',
                    'project_id' => 1,
                    'tracker_id' => 2,
                    'priority_id' => 3,
                    'status_id' => 4,
                    'description' => 'Description with some xml entities: & < > " \' ',
                ],
                '/issues.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issue>
                    <subject>Issue subject with some xml entities: &amp; &lt; &gt; " ' </subject>
                    <description>Description with some xml entities: &amp; &lt; &gt; " ' </description>
                    <project_id>1</project_id>
                    <priority_id>3</priority_id>
                    <status_id>4</status_id>
                    <tracker_id>2</tracker_id>
                </issue>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
            'test with all possible parameters' => [
                [
                    'project_id' => 1,
                    'tracker_id' => 2,
                    'status_id' => 3,
                    'priority_id' => 4,
                    'subject' => 'Issue subject',
                    'description' => 'Issue description',
                    'category_id' => 5,
                    'fixed_version_id' => 6,
                    'assigned_to_id' => 7,
                    'parent_issue_id' => 8,
                    'custom_fields' => [
                        [
                            'id' => 9,
                            'name' => 'Custom field 9',
                            'value' => 'value of cf9',
                        ],
                        [
                            'id' => 123,
                            'name' => 'cf_name',
                            'field_format' => 'string',
                            'value' => [1, 2, 3],
                        ],
                        [
                            'id' => 321,
                            'value' => 'https://example.com/?one=first&two=second',
                        ],
                    ],
                    'watcher_user_ids' => [10, 11],
                    'is_private' => false,
                    'estimated_hours' => 2.5,
                    'author_id' => 12,
                    'due_date' => '2024-12-31',
                    'start_date' => '2024-01-01',
                    'uploads' => [
                        [
                            'token' => '1.first-token',
                            'filename' => 'SomeRandomFile.txt',
                            'description' => 'Simple description',
                            'content_type' => 'text/plain',
                        ],
                        [
                            'token' => '2.second-token',
                            'filename' => 'An-Other-File.css',
                            'content_type' => 'text/css',
                        ],
                    ],
                ],
                '/issues.xml',
                <<<XML
                <?xml version="1.0" encoding="UTF-8"?>
                <issue>
                    <subject>Issue subject</subject>
                    <description>Issue description</description>
                    <project_id>1</project_id>
                    <category_id>5</category_id>
                    <priority_id>4</priority_id>
                    <status_id>3</status_id>
                    <tracker_id>2</tracker_id>
                    <assigned_to_id>7</assigned_to_id>
                    <author_id>12</author_id>
                    <due_date>2024-12-31</due_date>
                    <start_date>2024-01-01</start_date>
                    <watcher_user_ids type="array">
                        <watcher_user_id>10</watcher_user_id>
                        <watcher_user_id>11</watcher_user_id>
                    </watcher_user_ids>
                    <fixed_version_id>6</fixed_version_id>
                    <parent_issue_id>8</parent_issue_id>
                    <custom_fields type="array">
                        <custom_field id="9" name="Custom field 9">
                            <value>value of cf9</value>
                        </custom_field>
                        <custom_field id="123" name="cf_name" field_format="string" multiple="true">
                            <value type="array">
                                <value>1</value>
                                <value>2</value>
                                <value>3</value>
                            </value>
                        </custom_field>
                        <custom_field id="321">
                            <value>https://example.com/?one=first&amp;two=second</value>
                        </custom_field>
                    </custom_fields>
                    <estimated_hours>2.5</estimated_hours>
                    <uploads type="array">
                        <upload>
                            <token>1.first-token</token>
                            <filename>SomeRandomFile.txt</filename>
                            <description>Simple description</description>
                            <content_type>text/plain</content_type>
                        </upload>
                        <upload>
                            <token>2.second-token</token>
                            <filename>An-Other-File.css</filename>
                            <content_type>text/css</content_type>
                        </upload>
                    </uploads>
                </issue>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
        ];
    }

    public function testCreateReturnsEmptyString(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0" encoding="UTF-8"?><issue/>',
                500,
                '',
                '',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $return = $api->create([]);

        $this->assertSame('', $return);
    }

    public function testCreateWithHttpClientRetrievesIssueStatusId(): void
    {
        $client = AssertingHttpClient::create(
            $this,
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
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><status_id>123</status_id></issue>',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue></issue>',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $xmlElement = $api->create(['status' => 'Status Name']);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><issue></issue>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateWithHttpClientRetrievesProjectId(): void
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
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><project_id>3</project_id></issue>',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue></issue>',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $xmlElement = $api->create(['project' => 'Project Name']);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><issue></issue>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateWithHttpClientRetrievesIssueCategoryId(): void
    {
        $client = AssertingHttpClient::create(
            $this,
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
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><project_id>3</project_id><category_id>45</category_id></issue>',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue></issue>',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $xmlElement = $api->create(['project_id' => 3, 'category' => 'Category Name']);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><issue></issue>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateWithHttpClientRetrievesTrackerId(): void
    {
        $client = AssertingHttpClient::create(
            $this,
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
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><tracker_id>9</tracker_id></issue>',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue></issue>',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $xmlElement = $api->create(['tracker' => 'Tracker Name']);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><issue></issue>',
            $xmlElement->asXml(),
        );
    }

    public function testCreateWithHttpClientRetrievesUserId(): void
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
                '{"users":[{"login":"user_5","id":5},{"login":"user_6","id":6}]}',
            ],
            [
                'POST',
                '/issues.xml',
                'application/xml',
                '<?xml version="1.0"?><issue><assigned_to_id>6</assigned_to_id><author_id>5</author_id></issue>',
                200,
                'application/xml',
                '<?xml version="1.0"?><issue></issue>',
            ],
        );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $xmlElement = $api->create(['assigned_to' => 'user_6', 'author' => 'user_5']);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><issue></issue>',
            $xmlElement->asXml(),
        );
    }

    /**
     * Test cleanParams().
     */
    public function testCreateWithClientCleansParameters(): void
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
                '{"users":[{"login":"user_5","id":5},{"login":"user_6","id":6}]}',
            ],
            [
                'POST',
                '/issues.xml',
                'application/xml',
                <<<XML
                <?xml version="1.0"?>
                <issue>
                    <project_id>3</project_id>
                    <category_id>45</category_id>
                    <status_id>123</status_id>
                    <tracker_id>9</tracker_id>
                    <assigned_to_id>6</assigned_to_id>
                    <author_id>5</author_id>
                </issue>
                XML,
                200,
                'application/xml',
                '<?xml version="1.0"?><issue></issue>',
            ],
        );

        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'user_6',
            'author' => 'user_5',
        ];

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $xmlElement = $api->create($parameters);

        $this->assertInstanceOf(SimpleXMLElement::class, $xmlElement);
        $this->assertXmlStringEqualsXmlString(
            '<?xml version="1.0"?><issue></issue>',
            $xmlElement->asXml(),
        );
    }
}

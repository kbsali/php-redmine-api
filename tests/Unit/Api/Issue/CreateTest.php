<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\MissingParameterException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use SimpleXMLElement;

/**
 * @covers \Redmine\Api\Issue::create
 */
class CreateTest extends TestCase
{
    /**
     * @dataProvider getCreateData
     */
    public function testCreateReturnsCorrectResponse($parameters, $expectedPath, $expectedBody, $responseCode, $response)
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
                $response
            ]
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
                    ],
                    'watcher_user_ids' => [10, 11],
                    'is_private' => false,
                    'estimated_hours' => 2.5,
                    'author_id' => 12,
                    'due_date' => '2024-12-31',
                    'start_date' => '2024-01-01',
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
                    </custom_fields>
                    <estimated_hours>2.5</estimated_hours>
                </issue>
                XML,
                201,
                '<?xml version="1.0" encoding="UTF-8"?><issue></issue>',
            ],
        ];
    }
}

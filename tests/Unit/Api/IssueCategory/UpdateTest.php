<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\IssueCategory::update
 */
class UpdateTest extends TestCase
{
    /**
     * @dataProvider getUpdateData
     */
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
                $response
            ]
        );

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame('', $api->update($id, $parameters));
    }

    public static function getUpdateData(): array
    {
        return [
            'test update the name' => [
                5,
                [
                    'name' => 'new name',
                ],
                '/issue_categories/5.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue_category>
                    <name>new name</name>
                </issue_category>
                XML,
                204,
                '',
            ],
            'test assign user to category' => [
                5,
                [
                    'assigned_to_id' => 2,
                ],
                '/issue_categories/5.xml',
                <<< XML
                <?xml version="1.0"?>
                <issue_category>
                    <assigned_to_id>2</assigned_to_id>
                </issue_category>
                XML,
                204,
                '',
            ],
        ];
    }
}

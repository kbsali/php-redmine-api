<?php

namespace Redmine\Tests\Unit\Api\IssueCategory;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueCategory;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\IssueCategory::show
 */
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    public function testShowReturnsCorrectResponse($id, $expectedPath, $response, $expectedReturn)
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                $expectedPath,
                'application/json',
                '',
                200,
                'application/json',
                $response
            ]
        );

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($id));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [5, '/issue_categories/5.json', '["API Response"]', ['API Response']],
            'array response with string id' => ['5', '/issue_categories/5.json', '["API Response"]', ['API Response']],
            'string response' => [5, '/issue_categories/5.json', 'string', 'Error decoding body as JSON: Syntax error'],
            'false response' => [5, '/issue_categories/5.json', '', false],
        ];
    }
}

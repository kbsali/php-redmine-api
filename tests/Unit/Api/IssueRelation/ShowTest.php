<?php

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\IssueRelation::show
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
        $api = new IssueRelation($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($id));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [5, '/relations/5.json', '{"relation":{"child":[5,2,3]}}', ['child' => [5, 2, 3]]],
            'array response with string id' => ['5', '/relations/5.json', '{"relation":{"child":[5,2,3]}}', ['child' => [5, 2, 3]]],
            'array response on string error' => [5, '/relations/5.json', 'string', []],
            'array response on empty error' => [5, '/relations/5.json', '', []],
        ];
    }
}
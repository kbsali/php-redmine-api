<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Tests\Fixtures\AssertingHttpClient;

/**
 * @covers \Redmine\Api\Issue::show
 */
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($issueId, array $params, $expectedPath, $response, $expectedReturn)
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
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($issueId, $params));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [5, [], '/issues/5.json', '["API Response"]', ['API Response']],
            'array response with string id' => ['5', [], '/issues/5.json', '["API Response"]', ['API Response']],
            'array response with parameters' => [5, ['include' => ['parameter1', 'parameter2'], 'not-used'], '/issues/5.json?include=parameter1%2Cparameter2&0=not-used', '["API Response"]', ['API Response']],
            'string response' => [5, [], '/issues/5.json', 'string', 'Error decoding body as JSON: Syntax error'],
            'false response' => [5, [], '/issues/5.json', '', false],
        ];
    }
}

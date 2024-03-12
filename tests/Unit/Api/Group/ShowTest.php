<?php

namespace Redmine\Tests\Unit\Api\Group;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Group;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Group::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($groupId, array $params, $expectedPath, $response, $expectedReturn)
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
        $api = new Group($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($groupId, $params));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [5, [], '/groups/5.json', '["API Response"]', ['API Response']],
            'array response with string id' => ['5', [], '/groups/5.json', '["API Response"]', ['API Response']],
            'array response with parameters' => [5, ['include' => ['parameter1', 'parameter2'], 'not-used'], '/groups/5.json?include%5B%5D=parameter1&include%5B%5D=parameter2&0=not-used', '["API Response"]', ['API Response']],
            'string response' => [5, [], '/groups/5.json', 'string', 'Error decoding body as JSON: Syntax error'],
            'false response' => [5, [], '/groups/5.json', '', false],
        ];
    }
}

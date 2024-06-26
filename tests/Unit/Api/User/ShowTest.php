<?php

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(User::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($userId, array $params, $expectedPath, $response, $expectedReturn)
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
                $response,
            ]
        );

        // Create the object under test
        $api = new User($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($userId, $params));
    }

    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [5, [], '/users/5.json?include=memberships%2Cgroups', '["API Response"]', ['API Response']],
            'array response with string id' => ['5', [], '/users/5.json?include=memberships%2Cgroups', '["API Response"]', ['API Response']],
            'array response with parameters' => [5, ['parameter1', 'parameter2', 'memberships'], '/users/5.json?0=parameter1&1=parameter2&2=memberships&include=memberships%2Cgroups', '["API Response"]', ['API Response']],
            'string response' => [5, [], '/users/5.json?include=memberships%2Cgroups', 'string', 'Error decoding body as JSON: Syntax error'],
            'false response' => [5, [], '/users/5.json?include=memberships%2Cgroups', '', false],
        ];
    }
}

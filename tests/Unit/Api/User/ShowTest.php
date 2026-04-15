<?php

declare(strict_types=1);

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
     *
     * @param int|string $userId
     * @param array<mixed> $parameters
     * @param mixed $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($userId, array $parameters, string $expectedPath, string $response, $expectedReturn): void
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
            ],
        );

        // Create the object under test
        $api = User::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($userId, $parameters));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [
                5,
                [],
                '/users/5.json?include=memberships%2Cgroups',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with string id' => [
                '5',
                [],
                '/users/5.json?include=memberships%2Cgroups',
                '["API Response"]',
                ['API Response'],
            ],
            'array response with parameters' => [
                5,
                ['parameter1', 'parameter2', 'memberships'],
                '/users/5.json?0=parameter1&1=parameter2&2=memberships&include=memberships%2Cgroups',
                '["API Response"]',
                ['API Response'],
            ],
            'string response' => [
                5,
                [],
                '/users/5.json?include=memberships%2Cgroups',
                'string',
                /** @phpstan-ignore smaller.alwaysTrue(Remove this line after release of PHP 8.6) */
                (PHP_VERSION_ID < 80600) ? 'Error decoding body as JSON: Syntax error' : 'Error decoding body as JSON: Syntax error near location 1:1',
            ],
            'false response' => [
                5,
                [],
                '/users/5.json?include=memberships%2Cgroups',
                '',
                false,
            ],
        ];
    }
}

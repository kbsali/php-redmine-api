<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\User;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\User;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(User::class)]
class ShowTest extends TestCase
{
    /**
     * @param array<mixed> $parameters
     * @param array<mixed> $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse(int|string $userId, array $parameters, string $expectedPath, string $response, array $expectedReturn): void
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
        ];
    }

    #[DataProvider('getUnexpectedResponseData')]
    public function testShowWithUnexpectedResponseThrowsException(int $responseCode, string $responseContentType, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/users/5.json?include=memberships%2Cgroups',
                'application/json',
                '',
                $responseCode,
                $responseContentType,
                $response,
            ],
        );

        $api = User::fromHttpClient($client);


        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($responseCode, $e->getResponse()->getStatusCode());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getUnexpectedResponseData(): array
    {
        return [
            'status code 404 with empty body' => [404, 'application/json', ''],
            'status code 403 with empty body' => [403, 'application/json', ''],
            'status code 500 with HTML body' => [500, 'text/html', '<html><body>Internal Server Error</body></html>'],
            'status code 500 with JSON body' => [500, 'application/json', '["API Response"]'],
            'status code 200 with empty body' => [200, 'application/json', ''],
            'status code 200 with JSON null' => [200, 'application/json', 'null'],
            'status code 200 with JSON integer' => [200, 'application/json', '5'],
            'status code 200 with JSON string' => [200, 'application/json', '"text"'],
        ];
    }

    public function testShowWithInvalidJsonThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/users/5.json?include=memberships%2Cgroups',
                'application/json',
                '',
                200,
                'application/json',
                'string',
            ],
        );

        $api = User::fromHttpClient($client);


        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }
}

<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;

#[CoversClass(IssueRelation::class)]
class ShowTest extends TestCase
{
    /**
     * @dataProvider getShowData
     *
     * @param string|int $id
     * @param array<mixed> $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse($id, string $expectedPath, string $response, array $expectedReturn): void
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
        $api = IssueRelation::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show($id));
    }

    /**
     * @return array<array<mixed>>
     */
    public static function getShowData(): array
    {
        return [
            'array response with integer id' => [
                5,
                '/relations/5.json',
                '{"relation":{"child":[5,2,3]}}',
                ['child' => [5, 2, 3]],
            ],
            'array response with string id' => [
                '5',
                '/relations/5.json',
                '{"relation":{"child":[5,2,3]}}',
                ['child' => [5, 2, 3]],
            ],
            'array response on object without relation key error' => [
                5,
                '/relations/5.json',
                '{}',
                [],
            ],
            'array response on string error' => [
                5,
                '/relations/5.json',
                'string',
                [],
            ],
            'array response on empty error' => [
                5,
                '/relations/5.json',
                '',
                [],
            ],
        ];
    }

    public function testShowWithFutureModeReturnsCorrectResponse(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/relations/5.json',
                'application/json',
                '',
                200,
                'application/json',
                '{"relation":{"id":5}}',
            ],
        );

        $api = IssueRelation::fromHttpClient($client);

        FutureMode::enable($api);

        $this->assertSame(['id' => 5], $api->show(5));
    }

    public function testShowWithIncorrectStatusCodeReturnsEmptyArray(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/relations/5.json',
                'application/json',
                '',
                404,
                'application/json',
                '',
            ],
        );

        $api = IssueRelation::fromHttpClient($client);

        $this->assertSame([], $api->show(5));
    }

    /**
     * @dataProvider getUnexpectedResponseData
     */
    #[DataProvider('getUnexpectedResponseData')]
    public function testShowWithUnexpectedResponseThrowsException(int $responseCode, string $responseContentType, string $response): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/relations/5.json',
                'application/json',
                '',
                $responseCode,
                $responseContentType,
                $response,
            ],
        );

        $api = IssueRelation::fromHttpClient($client);

        FutureMode::enable($api);

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
            'status code 500 with JSON body' => [500, 'application/json', '{"relation":{"id":5}}'],
            'status code 200 with empty body' => [200, 'application/json', ''],
            'status code 200 with JSON null' => [200, 'application/json', 'null'],
            'status code 200 with JSON integer' => [200, 'application/json', '5'],
            'status code 200 with JSON string' => [200, 'application/json', '"text"'],
            'status code 200 with JSON object without relation key' => [200, 'application/json', '{}'],
            'status code 200 with JSON object with other key' => [200, 'application/json', '{"foo":{"id":5}}'],
            'status code 200 with JSON object with string relation' => [200, 'application/json', '{"relation":"text"}'],
            'status code 200 with JSON object with null relation' => [200, 'application/json', '{"relation":null}'],
        ];
    }

    public function testShowWithInvalidJsonThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/relations/5.json',
                'application/json',
                '',
                200,
                'application/json',
                'string',
            ],
        );

        $api = IssueRelation::fromHttpClient($client);

        FutureMode::enable($api);

        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }
}

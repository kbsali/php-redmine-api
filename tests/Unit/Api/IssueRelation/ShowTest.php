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

#[CoversClass(IssueRelation::class)]
final class ShowTest extends TestCase
{
    /**
     * @param array<mixed> $expectedReturn
     */
    #[DataProvider('getShowData')]
    public function testShowReturnsCorrectResponse(int|string $id, string $expectedPath, string $response, array $expectedReturn): void
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
     * @return \Iterator<int|string, mixed[]>
     */
    public static function getShowData(): \Iterator
    {
        yield 'array response with integer id' => [
            5,
            '/relations/5.json',
            '{"relation":{"child":[5,2,3]}}',
            ['child' => [5, 2, 3]],
        ];
        yield 'array response with string id' => [
            '5',
            '/relations/5.json',
            '{"relation":{"child":[5,2,3]}}',
            ['child' => [5, 2, 3]],
        ];
    }

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


        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($responseCode, $e->getResponse()->getStatusCode());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getUnexpectedResponseData(): \Iterator
    {
        yield 'status code 404 with empty body' => [404, 'application/json', ''];
        yield 'status code 403 with empty body' => [403, 'application/json', ''];
        yield 'status code 500 with HTML body' => [500, 'text/html', '<html><body>Internal Server Error</body></html>'];
        yield 'status code 500 with JSON body' => [500, 'application/json', '{"relation":{"id":5}}'];
        yield 'status code 200 with empty body' => [200, 'application/json', ''];
        yield 'status code 200 with JSON null' => [200, 'application/json', 'null'];
        yield 'status code 200 with JSON integer' => [200, 'application/json', '5'];
        yield 'status code 200 with JSON string' => [200, 'application/json', '"text"'];
        yield 'status code 200 with JSON object without relation key' => [200, 'application/json', '{}'];
        yield 'status code 200 with JSON object with other key' => [200, 'application/json', '{"foo":{"id":5}}'];
        yield 'status code 200 with JSON object with string relation' => [200, 'application/json', '{"relation":"text"}'];
        yield 'status code 200 with JSON object with null relation' => [200, 'application/json', '{"relation":null}'];
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


        try {
            $api->show(5);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame(200, $e->getResponse()->getStatusCode());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }
}

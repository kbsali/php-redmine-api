<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Api\ResponseValidationTrait;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Http\HttpFactory;
use Redmine\Http\Response;
use ReflectionMethod;
use ReflectionProperty;

final class ResponseValidationTraitTest extends TestCase
{
    /**
     * @param int[] $expectedStatusCodes
     */
    #[DataProvider('getExpectedStatusCodeData')]
    public function testThrowOnUnexpectedStatusCodeWithExpectedStatusCodeDoesNotThrow(int $statusCode, array $expectedStatusCodes): void
    {
        $this->assertNull($this->callTraitMethod(
            HttpFactory::makeResponse($statusCode, '', ''),
            'throwOnUnexpectedStatusCode',
            ...$expectedStatusCodes,
        ));
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getExpectedStatusCodeData(): \Iterator
    {
        yield 'one expected status code' => [201, [201]];
        yield 'first of several expected status codes' => [201, [201, 204]];
        yield 'last of several expected status codes' => [204, [201, 204]];
    }

    /**
     * @param int[] $expectedStatusCodes
     */
    #[DataProvider('getUnexpectedStatusCodeData')]
    public function testThrowOnUnexpectedStatusCodeWithUnexpectedStatusCodeThrowsException(int $statusCode, array $expectedStatusCodes): void
    {
        $response = HttpFactory::makeResponse($statusCode, 'application/json', '{"errors":["Name is invalid"]}');

        try {
            $this->callTraitMethod($response, 'throwOnUnexpectedStatusCode', ...$expectedStatusCodes);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($response, $e->getResponse());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getUnexpectedStatusCodeData(): \Iterator
    {
        yield 'one expected status code' => [422, [201]];
        yield 'several expected status codes' => [422, [201, 204]];
        yield 'other successful status code' => [200, [204]];
        yield 'no status code' => [0, [204]];
    }

    public function testGetJsonArrayOrThrowReturnsArray(): void
    {
        $this->assertSame(['group' => ['id' => 5]], $this->callTraitMethod(
            HttpFactory::makeResponse(200, 'application/json', '{"group":{"id":5}}'),
            'getJsonArrayOrThrow',
        ));
    }

    #[DataProvider('getUnexpectedJsonResponseData')]
    public function testGetJsonArrayOrThrowWithUnexpectedResponseThrowsException(int $statusCode, string $contentType, string $body): void
    {
        $response = HttpFactory::makeResponse($statusCode, $contentType, $body);

        try {
            $this->callTraitMethod($response, 'getJsonArrayOrThrow');
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($response, $e->getResponse());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getUnexpectedJsonResponseData(): \Iterator
    {
        yield 'status code 404 with empty body' => [404, 'application/json', ''];
        yield 'status code 500 with JSON body' => [500, 'application/json', '{"group":{"id":5}}'];
        yield 'status code 201 with JSON body' => [201, 'application/json', '{"group":{"id":5}}'];
        yield 'status code 200 with empty body' => [200, 'application/json', ''];
        yield 'status code 200 with JSON null' => [200, 'application/json', 'null'];
        yield 'status code 200 with JSON integer' => [200, 'application/json', '5'];
        yield 'status code 200 with JSON string' => [200, 'application/json', '"text"'];
    }

    public function testGetJsonArrayOrThrowWithInvalidJsonThrowsException(): void
    {
        $response = HttpFactory::makeResponse(200, 'application/json', 'string');

        try {
            $this->callTraitMethod($response, 'getJsonArrayOrThrow');
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($response, $e->getResponse());
            $this->assertInstanceOf(SerializerException::class, $e->getPrevious());
        }
    }

    /**
     * Call a private method of the trait on an API with the response as last response.
     */
    private function callTraitMethod(Response $response, string $methodName, int ...$arguments): mixed
    {
        $api = new class ($this->createStub(HttpClient::class)) extends AbstractApi {
            use ResponseValidationTrait;
        };

        $property = new ReflectionProperty($api, 'lastResponse');
        $method = new ReflectionMethod($api, $methodName);

        $property->setValue($api, $response);

        return $method->invoke($api, ...$arguments);
    }
}

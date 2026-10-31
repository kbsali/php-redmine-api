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

class ResponseValidationTraitTest extends TestCase
{
    /**
     * @dataProvider getExpectedStatusCodeData
     *
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
     * @return array<string, array<mixed>>
     */
    public static function getExpectedStatusCodeData(): array
    {
        return [
            'one expected status code' => [201, [201]],
            'first of several expected status codes' => [201, [201, 204]],
            'last of several expected status codes' => [204, [201, 204]],
        ];
    }

    /**
     * @dataProvider getUnexpectedStatusCodeData
     *
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
     * @return array<string, array<mixed>>
     */
    public static function getUnexpectedStatusCodeData(): array
    {
        return [
            'one expected status code' => [422, [201]],
            'several expected status codes' => [422, [201, 204]],
            'other successful status code' => [200, [204]],
            'no status code' => [0, [204]],
        ];
    }

    public function testGetJsonArrayOrThrowReturnsArray(): void
    {
        $this->assertSame(['group' => ['id' => 5]], $this->callTraitMethod(
            HttpFactory::makeResponse(200, 'application/json', '{"group":{"id":5}}'),
            'getJsonArrayOrThrow',
        ));
    }

    /**
     * @dataProvider getUnexpectedJsonResponseData
     */
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
     * @return array<string, array<mixed>>
     */
    public static function getUnexpectedJsonResponseData(): array
    {
        return [
            'status code 404 with empty body' => [404, 'application/json', ''],
            'status code 500 with JSON body' => [500, 'application/json', '{"group":{"id":5}}'],
            'status code 201 with JSON body' => [201, 'application/json', '{"group":{"id":5}}'],
            'status code 200 with empty body' => [200, 'application/json', ''],
            'status code 200 with JSON null' => [200, 'application/json', 'null'],
            'status code 200 with JSON integer' => [200, 'application/json', '5'],
            'status code 200 with JSON string' => [200, 'application/json', '"text"'],
        ];
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
     *
     * @return mixed
     */
    private function callTraitMethod(Response $response, string $methodName, int ...$arguments)
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

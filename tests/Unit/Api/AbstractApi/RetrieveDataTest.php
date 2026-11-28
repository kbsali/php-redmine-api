<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\AbstractApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Exception\SerializerException;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use ReflectionMethod;

#[CoversClass(AbstractApi::class)]
final class RetrieveDataTest extends TestCase
{
    /**
     * @param array<mixed> $expectedReturn
     */
    #[DataProvider('getRetrieveDataData')]
    public function testRetrieveDataReturnsCorrectResponse(string $path, string $contentType, int $responseCode, string $response, array $expectedReturn): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', $path, $contentType, '', $responseCode, $contentType, $response],
        ));

        $this->assertSame($expectedReturn, $this->retrieveData($api, $path));
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getRetrieveDataData(): \Iterator
    {
        yield 'JSON response' => ['/issues.json', 'application/json', 200, '{"issues":[]}', ['issues' => []]];
        yield 'XML response' => ['/issues.xml', 'application/xml', 200, '<?xml version="1.0"?><issues type="array"></issues>', ['@attributes' => ['type' => 'array']]];
    }

    #[DataProvider('getUnexpectedResponseData')]
    public function testRetrieveDataAndUnexpectedResponseThrowsException(int $responseCode, string $response): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', '/issues.json', 'application/json', '', $responseCode, 'application/json', $response],
        ));

        try {
            $this->retrieveData($api, '/issues.json');
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($api->getLastResponse(), $e->getResponse());
            $this->assertSame($responseCode, $e->getResponse()->getStatusCode());
            $this->assertNull($e->getPrevious());
        }
    }

    /**
     * @return \Iterator<string, mixed[]>
     */
    public static function getUnexpectedResponseData(): \Iterator
    {
        yield 'status code 403' => [403, '{"errors":["Status is invalid"]}'];
        yield 'status code 422' => [422, '{"errors":["Status is invalid"]}'];
    }

    public function testRetrieveDataAndUnexpectedResponseOnSecondPageThrowsException(): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issues.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                '{"issues":[],"total_count":150,"offset":0,"limit":100}',
            ],
            [
                'GET',
                '/issues.json?limit=50&offset=100',
                'application/json',
                '',
                500,
                'application/json',
                '{"errors":["Internal error"]}',
            ],
        ));

        try {
            $this->retrieveData($api, '/issues.json', ['limit' => 150]);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($api->getLastResponse(), $e->getResponse());
            $this->assertSame(500, $e->getResponse()->getStatusCode());
        }
    }

    public function testRetrieveDataAndSeveralPagesReturnsMergedResponse(): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issues.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                '{"issues":[{"id":1}],"total_count":150,"offset":0,"limit":100}',
            ],
            [
                'GET',
                '/issues.json?limit=50&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                '{"issues":[{"id":2}],"total_count":150,"offset":100,"limit":50}',
            ],
        ));

        $this->assertSame(
            [
                'issues' => [['id' => 1], ['id' => 2]],
                'total_count' => [150, 150],
                'offset' => [0, 100],
                'limit' => [100, 50],
            ],
            $this->retrieveData($api, '/issues.json', ['limit' => 150]),
        );
    }

    #[DataProvider('getRetrieveDataWithParamsData')]
    public function testRetrieveDataSendsOnlyNonEmptyParams(mixed $value, string $expectedPath): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', $expectedPath, 'application/json', '', 200, 'application/json', '{"issues":[]}'],
        ));

        $this->assertSame(['issues' => []], $this->retrieveData($api, '/issues.json', ['limit' => 1, 'foo' => $value]));
    }

    /**
     * @return \Iterator<string, array{mixed, string}>
     */
    public static function getRetrieveDataWithParamsData(): \Iterator
    {
        yield 'null is not sent' => [null, '/issues.json?limit=1&offset=0'];
        yield 'false is not sent' => [false, '/issues.json?limit=1&offset=0'];
        yield 'empty string is not sent' => ['', '/issues.json?limit=1&offset=0'];
        yield 'empty array is not sent' => [[], '/issues.json?limit=1&offset=0'];
        yield 'true is sent' => [true, '/issues.json?limit=1&offset=0&foo=1'];
        yield 'integer 0 is sent' => [0, '/issues.json?limit=1&offset=0&foo=0'];
        yield 'float 0.0 is sent' => [0.0, '/issues.json?limit=1&offset=0&foo=0'];
        yield 'string "0" is sent' => ['0', '/issues.json?limit=1&offset=0&foo=0'];
        yield 'string is sent' => ['string', '/issues.json?limit=1&offset=0&foo=string'];
        yield 'array with 0 is sent' => [[0], '/issues.json?limit=1&offset=0&foo%5B%5D=0'];
        yield 'array with "0" is sent' => [['0'], '/issues.json?limit=1&offset=0&foo%5B%5D=0'];
        yield 'array with empty string is sent' => [[''], '/issues.json?limit=1&offset=0&foo%5B%5D='];
    }

    #[DataProvider('getRequestContentTypeData')]
    public function testRetrieveDataSendsContentTypeGuessedFromPath(string $path, string $expectedContentType): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', $path, $expectedContentType, '', 200, 'application/json', '{}'],
        ));

        $this->assertSame([], $this->retrieveData($api, $path));
    }

    /**
     * @return \Iterator<string, array{string, string}>
     */
    public static function getRequestContentTypeData(): \Iterator
    {
        yield 'JSON path' => ['/issues.json', 'application/json'];
        yield 'JSON path with query' => ['/issues.json?limit=1', 'application/json'];
        yield 'XML path' => ['/issues.xml', 'application/xml'];
        yield 'JSON upload path' => ['/uploads.json', 'application/octet-stream'];
        yield 'XML upload path' => ['/uploads.xml', 'application/octet-stream'];
        yield 'other path' => ['/issues', ''];
    }

    #[DataProvider('getNotConvertibleResponseData')]
    public function testRetrieveDataAndNotConvertibleResponseThrowsException(string $contentType, string $response): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', '/issues.json', 'application/json', '', 200, $contentType, $response],
        ));

        $this->expectException(SerializerException::class);
        $this->expectExceptionMessage('Could not convert response body into array: ' . $response);

        $this->retrieveData($api, '/issues.json');
    }

    /**
     * @return \Iterator<string, array{string, string}>
     */
    public static function getNotConvertibleResponseData(): \Iterator
    {
        yield 'unsupported content type' => ['text/plain', 'not an array'];
        yield 'JSON scalar' => ['application/json', '12345'];
    }

    private function createApi(HttpClient $client): AbstractApi
    {
        return new class ($client) extends AbstractApi {};
    }

    /**
     * @param array<mixed> $params
     */
    private function retrieveData(AbstractApi $api, string $path, array $params = []): mixed
    {
        $method = new ReflectionMethod($api, 'retrieveData');

        return $method->invoke($api, $path, $params);
    }
}

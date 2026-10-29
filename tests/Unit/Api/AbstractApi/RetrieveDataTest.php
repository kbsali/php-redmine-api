<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\AbstractApi;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\AbstractApi;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;
use Redmine\Tests\Fixtures\FutureMode;
use ReflectionMethod;

#[CoversClass(AbstractApi::class)]
class RetrieveDataTest extends TestCase
{
    /**
     * @dataProvider getRetrieveDataData
     *
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
     * @dataProvider getRetrieveDataData
     *
     * @param array<mixed> $expectedReturn
     */
    #[DataProvider('getRetrieveDataData')]
    public function testRetrieveDataWithFutureModeReturnsCorrectResponse(string $path, string $contentType, int $responseCode, string $response, array $expectedReturn): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', $path, $contentType, '', $responseCode, $contentType, $response],
        ));
        FutureMode::enable($api);

        $this->assertSame($expectedReturn, $this->retrieveData($api, $path));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getRetrieveDataData(): array
    {
        return [
            'JSON response' => ['/issues.json', 'application/json', 200, '{"issues":[]}', ['issues' => []]],
            'XML response' => ['/issues.xml', 'application/xml', 200, '<?xml version="1.0"?><issues type="array"></issues>', ['@attributes' => ['type' => 'array']]],
        ];
    }

    /**
     * @dataProvider getUnexpectedResponseData
     */
    #[DataProvider('getUnexpectedResponseData')]
    public function testRetrieveDataWithUnexpectedResponseReturnsResponseData(int $responseCode, string $response): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', '/issues.json', 'application/json', '', $responseCode, 'application/json', $response],
        ));

        $this->assertSame(['errors' => ['Status is invalid']], $this->retrieveData($api, '/issues.json'));
    }

    /**
     * @dataProvider getUnexpectedResponseData
     */
    #[DataProvider('getUnexpectedResponseData')]
    public function testRetrieveDataWithFutureModeAndUnexpectedResponseThrowsException(int $responseCode, string $response): void
    {
        $api = $this->createApi(AssertingHttpClient::create(
            $this,
            ['GET', '/issues.json', 'application/json', '', $responseCode, 'application/json', $response],
        ));
        FutureMode::enable($api);

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
     * @return array<string, array<mixed>>
     */
    public static function getUnexpectedResponseData(): array
    {
        return [
            'status code 403' => [403, '{"errors":["Status is invalid"]}'],
            'status code 422' => [422, '{"errors":["Status is invalid"]}'],
        ];
    }

    public function testRetrieveDataWithFutureModeAndUnexpectedResponseOnSecondPageThrowsException(): void
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
        FutureMode::enable($api);

        try {
            $this->retrieveData($api, '/issues.json', ['limit' => 150]);
            $this->fail('Expected UnexpectedResponseException was not thrown.');
        } catch (UnexpectedResponseException $e) {
            $this->assertSame($api->getLastResponse(), $e->getResponse());
            $this->assertSame(500, $e->getResponse()->getStatusCode());
        }
    }

    public function testRetrieveDataWithFutureModeAndSeveralPagesReturnsMergedResponse(): void
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
        FutureMode::enable($api);

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

    private function createApi(HttpClient $client): AbstractApi
    {
        return new class ($client) extends AbstractApi {};
    }

    /**
     * @param array<mixed> $params
     *
     * @return mixed
     */
    private function retrieveData(AbstractApi $api, string $path, array $params = [])
    {
        $method = new ReflectionMethod($api, 'retrieveData');
        if (PHP_VERSION_ID < 80100) {
            $method->setAccessible(true);
        }

        return $method->invoke($api, $path, $params);
    }
}

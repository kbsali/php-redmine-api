<?php

namespace Redmine\Tests\Unit\Api\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Search;
use Redmine\Client\Client;
use Redmine\Http\HttpClient;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Search::class)]
class SearchTest extends TestCase
{
    public function testSearchTriggersDeprecationWarning(): void
    {
        $api = Search::fromHttpClient($this->createStub(HttpClient::class));

        // PHPUnit 10 compatible way to test trigger_error().
        set_error_handler(
            function ($errno, $errstr): bool {
                $this->assertSame(
                    '`Redmine\Api\Search::search()` is deprecated since v2.4.0, use `Redmine\Api\Search::listByQuery()` instead.',
                    $errstr,
                );

                restore_error_handler();
                return true;
            },
            E_USER_DEPRECATED,
        );

        $api->search('query');
    }

    /**
     * @dataProvider getAllData
     */
    #[DataProvider('getAllData')]
    public function testSearchReturnsClientGetResponse(string $response, string $responseType, $expectedResponse): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/search.json?limit=25&offset=0&q=query',
                'application/json',
                '',
                200,
                $responseType,
                $response,
            ],
        );

        // Create the object under test
        $api = Search::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->search('query'));
    }

    public static function getAllData(): array
    {
        return [
            'array response' => ['["API Response"]', 'application/json', ['API Response']],
            'string response' => ['"string"', 'application/json', 'Could not convert response body into array: "string"'],
            'false response' => ['', 'application/json', false],
        ];
    }

    public function testSearchReturnsClientGetResponseWithParameters(): void
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/search.json?limit=25&offset=0&0=not-used&q=query',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Search::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->search('query', $parameters));
    }
}

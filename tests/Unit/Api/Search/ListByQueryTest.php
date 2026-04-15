<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Search;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Search;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Search::class)]
class ListByQueryTest extends TestCase
{
    public function testListByQueryWithoutParametersReturnsResponse(): void
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/search.json?limit=25&offset=0&q=query',
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
        $this->assertSame($expectedReturn, $api->listByQuery('query'));
    }

    public function testListByQueryWithParametersReturnsResponse(): void
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
        $this->assertSame($expectedReturn, $api->listByQuery('query', $parameters));
    }

    public function testListByQueryThrowsException(): void
    {
        $response = '';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/search.json?limit=25&offset=0&q=query',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Search::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByQuery('query');
    }
}

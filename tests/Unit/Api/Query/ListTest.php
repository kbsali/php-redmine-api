<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\Query;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Query;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Query::class)]
class ListTest extends TestCase
{
    public function testListWithoutParametersReturnsResponse(): void
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/queries.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Query::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list());
    }

    public function testListWithParametersReturnsResponse(): void
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/queries.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Query::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($parameters));
    }

    public function testListThrowsException(): void
    {
        $response = '';

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/queries.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Query::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->list();
    }
}

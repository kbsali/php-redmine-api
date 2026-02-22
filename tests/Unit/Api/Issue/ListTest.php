<?php

namespace Redmine\Tests\Unit\Api\Issue;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(Issue::class)]
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
                '/issues.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

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
                '/issues.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($parameters));
    }

    public function testListThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issues.json',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
        );

        // Create the object under test
        $api = Issue::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->list();
    }
}

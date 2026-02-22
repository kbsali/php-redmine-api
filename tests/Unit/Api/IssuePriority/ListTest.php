<?php

namespace Redmine\Tests\Unit\Api\IssuePriority;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssuePriority;
use Redmine\Client\Client;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(IssuePriority::class)]
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
                '/enumerations/issue_priorities.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = IssuePriority::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list());
    }

    public function testListWithParametersReturnsResponse(): void
    {
        // Test values
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/issue_priorities.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = IssuePriority::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->list($allParameters));
    }

    public function testListThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/enumerations/issue_priorities.json',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
        );

        // Create the object under test
        $api = IssuePriority::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->list();
    }
}

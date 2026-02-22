<?php

namespace Redmine\Tests\Unit\Api\IssueRelation;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\IssueRelation;
use Redmine\Client\Client;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(IssueRelation::class)]
class ListByIssueIdTest extends TestCase
{
    public function testListByIssueIdWithoutParametersReturnsResponse(): void
    {
        // Test values
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issues/5/relations.json',
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
        $this->assertSame($expectedReturn, $api->listByIssueId(5));
    }

    public function testListByIssueIdWithParametersReturnsResponse(): void
    {
        // Test values
        $parameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issues/5/relations.json?limit=25&offset=0&0=not-used',
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
        $this->assertSame($expectedReturn, $api->listByIssueId(5, $parameters));
    }

    public function testListByIssueIdThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/issues/5/relations.json',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
        );

        // Create the object under test
        $api = IssueRelation::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->listByIssueId(5);
    }
}

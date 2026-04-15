<?php

declare(strict_types=1);

namespace Redmine\Tests\Unit\Api\CustomField;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Exception\UnexpectedResponseException;
use Redmine\Tests\Fixtures\AssertingHttpClient;

#[CoversClass(CustomField::class)]
class ListTest extends TestCase
{
    public function testListWithoutParametersReturnsResponse(): void
    {
        // Test values
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->list());
    }

    public function testListWithParametersReturnsResponse(): void
    {
        // Test values
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json?limit=25&offset=0&0=not-used',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->list($allParameters));
    }

    public function testListWithHighLimitParametersReturnsResponse(): void
    {
        // Test values
        $response = '{"limit":"100","items":[]}';
        $allParameters = ['limit' => 250];
        $expectedResponse = [
            'limit' => ['100', '100', '100'], // TODO: Check response created by array_merge_recursive()
            'items' => [],
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/custom_fields.json?limit=100&offset=100',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
            [
                'GET',
                '/custom_fields.json?limit=50&offset=200',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->list($allParameters));
    }

    public function testListCallsEndpointUntilOffsetIsHigherThanTotalCount(): void
    {
        // Test values
        $response = '{"limit":"100","offset":"10","total_count":"5","items":[]}';
        $allParameters = ['limit' => 250];
        $returnDataSet = [
            'limit' => '100',
            'offset' => '10',
            'total_count' => '5',
            'items' => [],
        ];

        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json?limit=100&offset=0',
                'application/json',
                '',
                200,
                'application/json',
                $response,
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        // Perform the tests
        $this->assertSame($returnDataSet, $api->list($allParameters));
    }

    public function testListThrowsException(): void
    {
        $client = AssertingHttpClient::create(
            $this,
            [
                'GET',
                '/custom_fields.json',
                'application/json',
                '',
                200,
                'application/json',
                '',
            ],
        );

        // Create the object under test
        $api = CustomField::fromHttpClient($client);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessage('The Redmine server replied with an unexpected response.');

        // Perform the tests
        $api->list();
    }
}

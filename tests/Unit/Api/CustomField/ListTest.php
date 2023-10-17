<?php

namespace Redmine\Tests\Unit\Api\CustomField;

use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Client\Client;

/**
 * @covers \Redmine\Api\CustomField::list
 */
class ListTest extends TestCase
{
    public function testListWithoutParametersReturnsResponse()
    {
        // Test values
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/custom_fields.json')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->list());
    }

    public function testListWithParametersReturnsResponse()
    {
        // Test values
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with('/custom_fields.json?limit=25&offset=0&0=not-used')
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->list($allParameters));
    }

    public function testListWithHighLimitParametersReturnsResponse()
    {
        // Test values
        $response = '{"limit":"100","items":[]}';
        $allParameters = ['limit' => 250];
        $expectedResponse = [
            'limit' => ['100', '100', '100'], // TODO: Check response created by array_merge_recursive()
            'items' => [],
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(3))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(3))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(3))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($expectedResponse, $api->list($allParameters));
    }

    public function testListCallsEndpointUntilOffsetIsHigherThanTotalCount()
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

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($returnDataSet, $api->list($allParameters));
    }
}

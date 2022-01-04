<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\CustomField;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\CustomField
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class CustomFieldTest extends TestCase
{
    /**
     * Test all().
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @covers ::isNotNull
     * @test
     */
    public function testAllReturnsClientGetResponse()
    {
        // Test values
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

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
        $this->assertSame($expectedResponse, $api->all());
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @covers ::isNotNull
     * @test
     */
    public function testAllReturnsClientGetResponseWithParameters()
    {
        // Test values
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringContains('not-used')
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
        $this->assertSame($expectedResponse, $api->all($allParameters));
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @covers ::isNotNull
     * @test
     */
    public function testAllReturnsClientGetResponseWithHighLimit()
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
        $this->assertSame($expectedResponse, $api->all($allParameters));
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @covers ::isNotNull
     * @test
     */
    public function testAllCallsEndpointUntilOffsetIsHigherThanTotalCount()
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
        $retrievedDataSet = $api->all($allParameters);
        $this->assertTrue(is_array($retrievedDataSet));
        $this->assertArrayHasKey('limit', $retrievedDataSet);
        $this->assertArrayHasKey('items', $retrievedDataSet);
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingReturnsNameIdArray()
    {
        // Test values
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
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
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetOnlyTheFirstTime()
    {
        // Test values
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
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
        $this->assertSame($expectedReturn, $api->listing());
        $this->assertSame($expectedReturn, $api->listing());
    }

    /**
     * Test listing().
     *
     * @covers ::listing
     * @test
     */
    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        // Test values
        $response = '{"custom_fields":[{"id":1,"name":"CustomField 1"},{"id":5,"name":"CustomField 5"}]}';
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(2))
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(2))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(2))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(true));
        $this->assertSame($expectedReturn, $api->listing(true));
    }

    /**
     * Test getIdByName().
     *
     * @covers ::getIdByName
     * @test
     */
    public function testGetIdByNameMakesGetRequest()
    {
        // Test values
        $response = '{"custom_fields":[{"id":5,"name":"CustomField 5"}]}';

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
        $this->assertFalse($api->getIdByName('CustomField 1'));
        $this->assertSame(5, $api->getIdByName('CustomField 5'));
    }
}

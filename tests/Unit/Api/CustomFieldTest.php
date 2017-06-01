<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\CustomField;

/**
 * @coversDefaultClass \Redmine\Api\CustomField
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class CustomFieldTest extends \PHPUnit\Framework\TestCase
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
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
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringContains('not-used')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame([$getResponse], $api->all($allParameters));
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
        $allParameters = ['limit' => 250];
        $returnDataSet = [
            'limit' => '100',
            'items' => [],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(3))
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($returnDataSet);

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $retrievedDataSet = $api->all($allParameters);
        $this->assertTrue(is_array($retrievedDataSet));
        $this->assertTrue(array_key_exists('limit', $retrievedDataSet));
        $this->assertTrue(array_key_exists('items', $retrievedDataSet));
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
        $allParameters = ['limit' => 250];
        $returnDataSet = [
            'limit' => '100',
            'offset' => '10',
            'total_count' => '5',
            'items' => [],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($returnDataSet);

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $retrievedDataSet = $api->all($allParameters);
        $this->assertTrue(is_array($retrievedDataSet));
        $this->assertTrue(array_key_exists('limit', $retrievedDataSet));
        $this->assertTrue(array_key_exists('items', $retrievedDataSet));
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
        $getResponse = [
            'custom_fields' => [
                ['id' => 1, 'name' => 'CustomField 1'],
                ['id' => 5, 'name' => 'CustomField 5'],
            ],
        ];
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($getResponse);

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
        $getResponse = [
            'custom_fields' => [
                ['id' => 1, 'name' => 'CustomField 1'],
                ['id' => 5, 'name' => 'CustomField 5'],
            ],
        ];
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($getResponse);

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
        $getResponse = [
            'custom_fields' => [
                ['id' => 1, 'name' => 'CustomField 1'],
                ['id' => 5, 'name' => 'CustomField 5'],
            ],
        ];
        $expectedReturn = [
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($getResponse);

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
        $getResponse = [
            'custom_fields' => [
                ['id' => 5, 'name' => 'CustomField 5'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/custom_fields.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertFalse($api->getIdByName('CustomField 1'));
        $this->assertSame(5, $api->getIdByName('CustomField 5'));
    }
}

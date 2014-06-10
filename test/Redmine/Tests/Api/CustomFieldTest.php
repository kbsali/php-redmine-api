<?php

namespace Redmine\Tests\Api;

use Redmine\Api\CustomField;

/**
 * @coversDefaultClass Redmine\Api\CustomField
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class CustomFieldTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test all()
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @test
     *
     * @return void
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
     * Test all()
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @test
     *
     * @return void
     */
    public function testAllReturnsClientGetResponseWithParameters()
    {
        // Test values
        $allParameters = array('not-used');
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->stringContains('not-used')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new CustomField($client);

        // Perform the tests
        $this->assertSame(array($getResponse), $api->all($allParameters));
    }

    /**
     * Test all()
     *
     * @covers ::all
     * @covers ::get
     * @covers ::retrieveAll
     * @test
     *
     * @return void
     */
    public function testAllReturnsClientGetResponseWithHighLimit()
    {
        // Test values
        $allParameters = array('limit' => 250);
        $getResponse = 'API Response';

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
        $this->assertSame(array($getResponse), $api->all($allParameters));
    }

    /**
     * Test listing()
     *
     * @covers ::listing
     * @test
     *
     * @return void
     */
    public function testListingReturnsNameIdArray()
    {
        // Test values
        $getResponse = array(
            'custom_fields' => array(
                array('id' => 1, 'name' => 'CustomField 1'),
                array('id' => 5, 'name' => 'CustomField 5')
            ),
        );
        $expectedReturn = array(
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        );

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
     * Test listing()
     *
     * @covers ::listing
     * @test
     *
     * @return void
     */
    public function testListingCallsGetOnlyTheFirstTime()
    {
        // Test values
        $getResponse = array(
            'custom_fields' => array(
                array('id' => 1, 'name' => 'CustomField 1'),
                array('id' => 5, 'name' => 'CustomField 5')
            ),
        );
        $expectedReturn = array(
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        );

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
     * Test listing()
     *
     * @covers ::listing
     * @test
     *
     * @return void
     */
    public function testListingCallsGetEveryTimeWithForceUpdate()
    {
        // Test values
        $getResponse = array(
            'custom_fields' => array(
                array('id' => 1, 'name' => 'CustomField 1'),
                array('id' => 5, 'name' => 'CustomField 5')
            ),
        );
        $expectedReturn = array(
            'CustomField 1' => 1,
            'CustomField 5' => 5,
        );

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
     * Test getIdByName()
     *
     * @covers ::getIdByName
     * @test
     *
     * @return void
     */
    public function testGetIdByNameMakesGetRequest()
    {
        // Test values
        $getResponse = array(
            'custom_fields' => array(
                array('id' => 5, 'name' => 'CustomField 5')
            ),
        );

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

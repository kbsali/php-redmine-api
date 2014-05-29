<?php
/**
 * CustomField API test
 *
 * PHP version 5.4
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 * @copyright  2014 Malte Gerth
 * @license    MIT
 * @link       https://github.com/kbsali/php-redmine-api
 * @since      2014-05-29
 */

namespace Redmine\Tests\Api;

use Redmine\Api\CustomField;

/**
 * CustomField API test
 *
 * @coversDefaultClass Redmine\Api\CustomField
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 * @copyright  2014 Malte Gerth
 * @license    MIT
 * @link       https://github.com/kbsali/php-redmine-api
 * @since      2014-05-29
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
}

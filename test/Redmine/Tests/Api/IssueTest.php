<?php
/**
 * Issue API test
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

use Redmine\Api\Issue;

/**
 * Issue API test
 *
 * @coversDefaultClass Redmine\Api\Issue
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 * @copyright  2014 Malte Gerth
 * @license    MIT
 * @link       https://github.com/kbsali/php-redmine-api
 * @since      2014-05-29
 */
class IssueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test all()
     *
     * @covers ::all
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
                $this->stringStartsWith('/issues.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
    }

    /**
     * Test all()
     *
     * @covers ::all
     * @test
     *
     * @return void
     */
    public function testAllReturnsClientGetResponseWithParameters()
    {
        // Test values
        $parameters = array('not-used');
        $getResponse = array('API Response');

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->any())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($parameters));
    }

    /**
     * Test show()
     *
     * @covers ::get
     * @covers ::show
     * @test
     *
     * @return void
     */
    public function testShowReturnsClientGetResponse()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with($this->stringStartsWith('/issues/5.json'))
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
    }

    /**
     * Test show()
     *
     * @covers ::get
     * @covers ::show
     * @test
     *
     * @return void
     */
    public function testShowCallsGetUrlWithParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $allParameters = array('not-used');

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5, $allParameters));
    }

    /**
     * Test remove()
     *
     * @covers ::delete
     * @covers ::remove
     * @test
     *
     * @return void
     */
    public function testRemoveCallsDelete()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('delete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->remove(5));
    }

    /**
     * Test attach()
     *
     * @covers ::attach
     * @covers ::put
     * @test
     *
     * @return void
     */
    public function testAttachCallsPut()
    {
        // Test values
        $response = 'API Response';
        $attachment = array(
            'token' => 'sample-test-token',
            'filename' => 'test.txt'
        );
        $requestData = array(
            'issue' => array(
                'id' => 5,
                'uploads' => array(
                    'upload' => $attachment
                )
            )
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                ),
                json_encode($requestData)
            )
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->attach(5, $attachment));
    }
}

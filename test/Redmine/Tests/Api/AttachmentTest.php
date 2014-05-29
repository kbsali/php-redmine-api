<?php
/**
 * Attachment API test
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

use Redmine\Api\Attachment;

/**
 * Attachment API test
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 * @copyright  2014 Malte Gerth
 * @license    MIT
 * @link       https://github.com/kbsali/php-redmine-api
 * @since      2014-05-29
 */
class AttachmentTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test lastCallFailed()
     *
     * @dataProvider responseCodeProvider
     * @test
     *
     * @return void
     */
    public function testLastCallFailedTrue($responseCode, $hasFailed)
    {
        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('getResponseCode')
            ->willReturn($responseCode);

        // Create the object under test
        $api = new Attachment($client);

        // Perform the tests
        $this->assertSame($hasFailed, $api->lastCallFailed());
    }

    /**
     * Data provider for response code and expected state
     *
     * @return array[]
     */
    public function responseCodeProvider()
    {
        return array(
            array(199, true),
            array(200, false),
            array(201, false),
            array(202, true),
            array(400, true),
            array(403, true),
            array(404, true),
            array(500, true),
        );
    }
}

<?php
/**
 * IssueCategory API test
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

use Redmine\Api\IssueCategory;

/**
 * IssueCategory API test
 *
 * @coversDefaultClass Redmine\Api\IssueCategory
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 * @copyright  2014 Malte Gerth
 * @license    MIT
 * @link       https://github.com/kbsali/php-redmine-api
 * @since      2014-05-29
 */
class IssueCategoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test all()
     *
     * @covers ::all
     * @test
     *
     * @return void
     */
    public function testAllReturnsClientGetResponseWithProject()
    {
        // Test values
        $projectId = 5;
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($projectId));
    }

    /**
     * Test all()
     *
     * @covers ::all
     * @test
     *
     * @return void
     */
    public function testAllReturnsClientGetResponseWithParametersAndProject()
    {
        // Test values
        $projectId = 5;
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
                    $this->stringStartsWith('/projects/5/issue_categories.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all($projectId, $parameters));
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
            'issue_categories' => array(
                array('id' => 1, 'name' => 'IssueCategory 1'),
                array('id' => 5, 'name' => 'IssueCategory 5')
            ),
        );
        $expectedReturn = array(
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->atLeastOnce())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
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
            'issue_categories' => array(
                array('id' => 1, 'name' => 'IssueCategory 1'),
                array('id' => 5, 'name' => 'IssueCategory 5')
            ),
        );
        $expectedReturn = array(
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5));
        $this->assertSame($expectedReturn, $api->listing(5));
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
            'issue_categories' => array(
                array('id' => 1, 'name' => 'IssueCategory 1'),
                array('id' => 5, 'name' => 'IssueCategory 5')
            ),
        );
        $expectedReturn = array(
            'IssueCategory 1' => 1,
            'IssueCategory 5' => 5,
        );

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->stringStartsWith('/projects/5/issue_categories')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->listing(5, true));
        $this->assertSame($expectedReturn, $api->listing(5, true));
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
            ->with($this->stringStartsWith('/issue_categories/5.json'))
            ->willReturn($getResponse);

        // Create the object under test
        $api = new IssueCategory($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5));
    }
}

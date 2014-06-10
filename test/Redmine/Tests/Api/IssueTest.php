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
     * Test the constants
     *
     * @test
     *
     * @return void
     */
    public function testPriorityConstants()
    {
        $this->assertSame(1, Issue::PRIO_LOW);
        $this->assertSame(2, Issue::PRIO_NORMAL);
        $this->assertSame(3, Issue::PRIO_HIGH);
        $this->assertSame(4, Issue::PRIO_URGENT);
        $this->assertSame(5, Issue::PRIO_IMMEDIATE);
    }

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

    /**
     * Test addWatcher()
     *
     * @covers ::addWatcher
     * @test
     *
     * @return void
     */
    public function testAddWatcherCallsPost()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                $this->stringStartsWith('/issues/5/watchers.xml'),
                $this->stringEndsWith('<user_id>10</user_id>')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->addWatcher(5, 10));
    }

    /**
     * Test removeWatcher()
     *
     * @covers ::removeWatcher
     * @test
     *
     * @return void
     */
    public function testRemoveWatcherCallsPost()
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
                $this->stringStartsWith('/issues/5/watchers/10.xml')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->removeWatcher(5, 10));
    }

    /**
     * Test create()
     *
     * @covers ::create
     * @covers ::post
     * @test
     *
     * @return void
     */
    public function testCreateCallsPost()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = array();

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                '<?xml version="1.0"?>' . PHP_EOL . '<issue/>' . PHP_EOL
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test cleanParams()
     *
     * @covers ::create
     * @covers ::cleanParams
     * @test
     *
     * @return void
     */
    public function testCreateCleansParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = array(
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        );

        // Create the used mock objects
        $getIdByNameApi = $this->getMockBuilder('Redmine\Api\Project')
            ->disableOriginalConstructor()
            ->getMock();
        $getIdByNameApi->expects($this->exactly(4))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $getIdByUsernameApi = $this->getMockBuilder('Redmine\Api\User')
            ->disableOriginalConstructor()
            ->getMock();
        $getIdByUsernameApi->expects($this->exactly(2))
            ->method('getIdByUsername')
            ->willReturn('cleanedValue');

        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(6))
            ->method('api')
            ->willReturnMap(
                array(
                    array('project', $getIdByNameApi),
                    array('issue_category', $getIdByNameApi),
                    array('issue_status', $getIdByNameApi),
                    array('tracker', $getIdByNameApi),
                    array('user', $getIdByUsernameApi),
                )
            );

        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . PHP_EOL . '<issue>'),
                    $this->stringEndsWith('</issue>' . PHP_EOL),
                    $this->stringContains('<project_id>cleanedValue</project_id>'),
                    $this->stringContains('<category_id>cleanedValue</category_id>'),
                    $this->stringContains('<status_id>cleanedValue</status_id>'),
                    $this->stringContains('<tracker_id>cleanedValue</tracker_id>'),
                    $this->stringContains('<assigned_to_id>cleanedValue</assigned_to_id>'),
                    $this->stringContains('<author_id>cleanedValue</author_id>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test update()
     *
     * @covers ::update
     * @covers ::put
     * @test
     *
     * @return void
     */
    public function testUpdateCallsPut()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = array();

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issues/5.xml',
                '<?xml version="1.0"?>' . PHP_EOL . '<issue><id>5</id></issue>' . PHP_EOL
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }

    /**
     * Test update()
     *
     * @covers ::update
     * @covers ::cleanParams
     * @test
     *
     * @return void
     */
    public function testUpdateCleansParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = array(
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        );

        // Create the used mock objects
        $getIdByNameApi = $this->getMockBuilder('Redmine\Api\Project')
            ->disableOriginalConstructor()
            ->getMock();
        $getIdByNameApi->expects($this->exactly(4))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $getIdByUsernameApi = $this->getMockBuilder('Redmine\Api\User')
            ->disableOriginalConstructor()
            ->getMock();
        $getIdByUsernameApi->expects($this->exactly(2))
            ->method('getIdByUsername')
            ->willReturn('cleanedValue');

        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->exactly(6))
            ->method('api')
            ->willReturnMap(
                array(
                    array('project', $getIdByNameApi),
                    array('issue_category', $getIdByNameApi),
                    array('issue_status', $getIdByNameApi),
                    array('tracker', $getIdByNameApi),
                    array('user', $getIdByUsernameApi),
                )
            );

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>' . PHP_EOL . '<issue>'),
                    $this->stringEndsWith('</issue>' . PHP_EOL),
                    $this->stringContains('<project_id>cleanedValue</project_id>'),
                    $this->stringContains('<category_id>cleanedValue</category_id>'),
                    $this->stringContains('<status_id>cleanedValue</status_id>'),
                    $this->stringContains('<tracker_id>cleanedValue</tracker_id>'),
                    $this->stringContains('<assigned_to_id>cleanedValue</assigned_to_id>'),
                    $this->stringContains('<author_id>cleanedValue</author_id>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }
}

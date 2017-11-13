<?php

namespace Redmine\Tests\Unit\Api;

use Redmine\Api\Issue;

/**
 * @coversDefaultClass \Redmine\Api\Issue
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssueTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the constants.
     *
     * @test
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
     * Test all().
     *
     * @covers ::all
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
                $this->stringStartsWith('/issues.json')
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->all());
    }

    /**
     * Test all().
     *
     * @covers ::all
     * @test
     */
    public function testAllReturnsClientGetResponseWithParameters()
    {
        // Test values
        $parameters = ['not-used'];
        $getResponse = ['API Response'];

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
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
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
     * Test show().
     *
     * @covers ::get
     * @covers ::show
     * @test
     */
    public function testShowCallsGetUrlWithParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $allParameters = ['not-used'];

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
     * Test show().
     *
     * @covers ::show
     * @test
     */
    public function testShowImplodesIncludeParametersCorrectly()
    {
        // Test values
        $parameters = ['include' => ['parameter1', 'parameter2']];
        $getResponse = ['API Response'];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('get')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5.json'),
                    $this->stringContains(urlencode('parameter1,parameter2'))
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->show(5, $parameters));
    }

    /**
     * Test remove().
     *
     * @covers ::delete
     * @covers ::remove
     * @test
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
     * Test attach().
     *
     * @covers ::attach
     * @covers ::put
     * @test
     */
    public function testAttachCallsPut()
    {
        // Test values
        $response = 'API Response';
        $attachment = [
            'token' => 'sample-test-token',
            'filename' => 'test.txt',
        ];
        $requestData = [
            'issue' => [
                'id' => 5,
                'uploads' => [$attachment],
            ],
        ];

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
     * Test addWatcher().
     *
     * @covers ::addWatcher
     * @test
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
     * Test removeWatcher().
     *
     * @covers ::removeWatcher
     * @test
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
     * Test create().
     *
     * @covers ::create
     * @covers ::post
     * @test
     */
    public function testCreateCallsPost()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                '<?xml version="1.0"?>'."\n".'<issue/>'."\n"
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test cleanParams().
     *
     * @covers ::create
     * @covers ::cleanParams
     * @test
     */
    public function testCreateCleansParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        ];

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
            ->setMethods(['api', 'post'])
            ->getMock();
        $client->expects($this->exactly(6))
            ->method('api')
            ->willReturnMap(
                [
                    ['project', $getIdByNameApi],
                    ['issue_category', $getIdByNameApi],
                    ['issue_status', $getIdByNameApi],
                    ['tracker', $getIdByNameApi],
                    ['user', $getIdByUsernameApi],
                ]
            );

        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
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
     * Test create() and buildXML().
     *
     * @covers ::create
     * @covers ::buildXML
     * @covers ::attachCustomFieldXML
     * @test
     */
    public function testCreateBuildsXmlForCustomFields()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'custom_fields' => [
                [
                    'id' => 123,
                    'name' => 'cf_name',
                    'field_format' => 'string',
                    'value' => [1, 2, 3],
                ],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();

        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'),
                    $this->stringContains('<issue>'),
                    $this->stringContains('<custom_fields type="array">'),
                    $this->stringContains('<custom_field name="cf_name" field_format="string" id="123" multiple="true">'),
                    $this->stringContains('<value>1</value>'),
                    $this->stringContains('<value>2</value>'),
                    $this->stringContains('<value>3</value>'),
                    $this->stringContains('</custom_field>'),
                    $this->stringContains('</custom_fields>'),
                    $this->stringEndsWith('</issue>'."\n")
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->create($parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @covers ::put
     * @test
     */
    public function testUpdateCallsPut()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issues/5.xml',
                '<?xml version="1.0"?>'."\n".'<issue><id>5</id></issue>'."\n"
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->update(5, $parameters));
    }

    /**
     * Test update().
     *
     * @covers ::update
     * @covers ::cleanParams
     * @test
     */
    public function testUpdateCleansParameters()
    {
        // Test values
        $getResponse = 'API Response';
        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        ];

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
            ->setMethods(['api', 'put'])
            ->getMock();
        $client->expects($this->exactly(6))
            ->method('api')
            ->willReturnMap(
                [
                    ['project', $getIdByNameApi],
                    ['issue_category', $getIdByNameApi],
                    ['issue_status', $getIdByNameApi],
                    ['tracker', $getIdByNameApi],
                    ['user', $getIdByUsernameApi],
                ]
            );

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<id>5</id>'),
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

    /**
     * Test setIssueStatus().
     *
     * @covers ::setIssueStatus
     * @test
     */
    public function testSetIssueStatus()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $issueStatusApi = $this->getMockBuilder('Redmine\Api\Project')
            ->disableOriginalConstructor()
            ->getMock();
        $issueStatusApi->expects($this->once())
            ->method('getIdByName')
            ->willReturn(123);

        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->setMethods(['api', 'put'])
            ->getMock();
        $client->expects($this->once())
            ->method('api')
            ->with('issue_status')
            ->willReturn($issueStatusApi);

        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<id>5</id>'),
                    $this->stringContains('<status_id>123</status_id>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->setIssueStatus(5, 'Status Name'));
    }

    /**
     * Test addNoteToIssue().
     *
     * @covers ::addNoteToIssue
     * @test
     */
    public function testAddNoteToIssue()
    {
        // Test values
        $getResponse = 'API Response';

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('put')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<id>5</id>'),
                    $this->stringContains('<notes>Note content</notes>')
                )
            )
            ->willReturn($getResponse);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($getResponse, $api->addNoteToIssue(5, 'Note content'));
    }

    /**
     * Test buildXML().
     *
     * @covers ::buildXML
     * @test
     */
    public function testBuildXmlWithCustomFields()
    {
        // Test values
        $parameters = [
            'custom_fields' => [
                ['id' => 225, 'value' => 'One Custom Field'],
                ['id' => 25, 'value' => 'Second Custom Field'],
                ['id' => 321, 'value' => 'http://test.local/?one=first&two=second'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<custom_fields type="array">'),
                    $this->stringContains('</custom_fields>'),
                    $this->stringContains('<custom_field id="225"><value>One Custom Field</value></custom_field>'),
                    $this->stringContains('<custom_field id="25"><value>Second Custom Field</value></custom_field>'),
                    $this->stringContains('<custom_field id="321"><value>http://test.local/?one=first&amp;two=second</value></custom_field>')
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Test buildXML().
     *
     * @covers ::buildXML
     * @test
     */
    public function testBuildXmlWithWatchers()
    {
        // Test values
        $parameters = [
            'watcher_user_ids' => [5, 25],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<watcher_user_ids type="array">'),
                    $this->stringContains('</watcher_user_ids>'),
                    $this->stringContains('<watcher_user_id>5</watcher_user_id>'),
                    $this->stringContains('<watcher_user_id>25</watcher_user_id>')
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Test buildXML().
     *
     * @covers ::buildXML
     * @test
     */
    public function testBuildXmlWithUploads()
    {
        // Test values
        $parameters = [
            'uploads' => [
                [
                    'token' => 'first-token',
                    'filename' => 'SomeRandomFile.txt',
                    'description' => 'Simple description',
                    'content_type' => 'text/plain',
                ],
                [
                    'token' => 'second-token',
                    'filename' => 'An-Other-File.css',
                    'content_type' => 'text/css',
                ],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<uploads type="array">'),
                    $this->stringContains('</uploads>'),
                    $this->stringContains(
                        '<upload>'
                        .'<token>first-token</token>'
                        .'<filename>SomeRandomFile.txt</filename>'
                        .'<description>Simple description</description>'
                        .'<content_type>text/plain</content_type>'
                        .'</upload>'
                    ),
                    $this->stringContains(
                        '<upload>'
                        .'<token>second-token</token>'
                        .'<filename>An-Other-File.css</filename>'
                        .'<content_type>text/css</content_type>'
                        .'</upload>'
                    )
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Test buildXML().
     *
     * @covers ::buildXML
     * @test
     */
    public function testBuildXmlWithWatcherAndUploadAndCustomFieldAndStandard()
    {
        // Test values
        $parameters = [
            'watcher_user_ids' => [5],
            'subject' => 'Issue subject',
            'uploads' => [
                [
                    'token' => 'first-token',
                    'filename' => 'SomeRandomFile.txt',
                    'description' => 'Simple description',
                    'content_type' => 'text/plain',
                ],
            ],
            'custom_fields' => [
                ['id' => 25, 'value' => 'Second Custom Field'],
            ],
        ];

        // Create the used mock objects
        $client = $this->getMockBuilder('Redmine\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $client->expects($this->once())
            ->method('post')
            ->with(
                '/issues.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<watcher_user_ids type="array">'),
                    $this->stringContains('</watcher_user_ids>'),
                    $this->stringContains('<watcher_user_id>5</watcher_user_id>'),
                    $this->stringContains(
                        '<upload>'
                        .'<token>first-token</token>'
                        .'<filename>SomeRandomFile.txt</filename>'
                        .'<description>Simple description</description>'
                        .'<content_type>text/plain</content_type>'
                        .'</upload>'
                    ),
                    $this->stringContains(
                        '<custom_field id="25">'
                        .'<value>Second Custom Field</value>'
                        .'</custom_field>'
                    ),
                    $this->stringContains('<subject>Issue subject</subject>')
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->create($parameters);
    }
}

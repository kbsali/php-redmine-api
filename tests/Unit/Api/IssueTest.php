<?php

namespace Redmine\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue;
use Redmine\Client\Client;

/**
 * @coversDefaultClass \Redmine\Api\Issue
 *
 * @author     Malte Gerth <mail@malte-gerth.de>
 */
class IssueTest extends TestCase
{
    public function getPriorityConstantsData()
    {
        return [
            [1, Issue::PRIO_LOW],
            [2, Issue::PRIO_NORMAL],
            [3, Issue::PRIO_HIGH],
            [4, Issue::PRIO_URGENT],
            [5, Issue::PRIO_IMMEDIATE],
        ];
    }

    /**
     * Test the constants.
     *
     * @dataProvider getPriorityConstantsData
     *
     * @test
     */
    public function testPriorityConstants($expected, $value)
    {
        $this->assertSame($expected, $value);
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->stringStartsWith('/issues.json')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all());
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->all($parameters));
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with($this->stringStartsWith('/issues/5.json'))
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show(5));
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
        $allParameters = ['not-used'];
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5.json'),
                    $this->stringContains('not-used')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show(5, $allParameters));
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
        $response = '["API Response"]';
        $expectedReturn = ['API Response'];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestGet')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5.json'),
                    $this->stringContains(urlencode('parameter1,parameter2'))
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);
        $client->expects($this->exactly(1))
            ->method('getLastResponseContentType')
            ->willReturn('application/json');

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($expectedReturn, $api->show(5, $parameters));
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->logicalAnd(
                    $this->stringStartsWith('/issues/5'),
                    $this->logicalXor(
                        $this->stringEndsWith('.json'),
                        $this->stringEndsWith('.xml')
                    )
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->remove(5));
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
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
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                $this->stringStartsWith('/issues/5/watchers.xml'),
                $this->stringEndsWith('<user_id>10</user_id>')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->addWatcher(5, 10));
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestDelete')
            ->with(
                $this->stringStartsWith('/issues/5/watchers/10.xml')
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->removeWatcher(5, 10));
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
        $response = 'API Response';
        $parameters = [];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
            ->with(
                '/issues.xml',
                '<?xml version="1.0"?>'."\n".'<issue/>'."\n"
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->create($parameters));
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
        $response = 'API Response';
        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        ];

        // Create the used mock objects
        $getIdByNameApi = $this->createMock('Redmine\Api\Project');
        $getIdByNameApi->expects($this->exactly(3))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $issueCategoryGetIdByNameApi = $this->createMock('Redmine\Api\IssueCategory');
        $issueCategoryGetIdByNameApi->expects($this->exactly(1))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $getIdByUsernameApi = $this->createMock('Redmine\Api\User');
        $getIdByUsernameApi->expects($this->exactly(2))
            ->method('getIdByUsername')
            ->willReturn('cleanedValue');

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(6))
            ->method('getApi')
            ->willReturnMap(
                [
                    ['project', $getIdByNameApi],
                    ['issue_category', $issueCategoryGetIdByNameApi],
                    ['issue_status', $getIdByNameApi],
                    ['tracker', $getIdByNameApi],
                    ['user', $getIdByUsernameApi],
                ]
            );

        $client->expects($this->once())
            ->method('requestPost')
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
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->create($parameters));
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
        $response = 'API Response';
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
        $client = $this->createMock(Client::class);

        $client->expects($this->once())
            ->method('requestPost')
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
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->create($parameters));
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
        $response = 'API Response';
        $parameters = [];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/issues/5.xml',
                '<?xml version="1.0"?>'."\n".'<issue><id>5</id></issue>'."\n"
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
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
        $response = 'API Response';
        $parameters = [
            'project' => 'Project Name',
            'category' => 'Category Name',
            'status' => 'Status Name',
            'tracker' => 'Tracker Name',
            'assigned_to' => 'Assigned to User Name',
            'author' => 'Author Name',
        ];

        // Create the used mock objects
        $getIdByNameApi = $this->createMock('Redmine\Api\Project');
        $getIdByNameApi->expects($this->exactly(3))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $issueCategoryGetIdByNameApi = $this->createMock('Redmine\Api\IssueCategory');
        $issueCategoryGetIdByNameApi->expects($this->exactly(1))
            ->method('getIdByName')
            ->willReturn('cleanedValue');
        $getIdByUsernameApi = $this->createMock('Redmine\Api\User');
        $getIdByUsernameApi->expects($this->exactly(2))
            ->method('getIdByUsername')
            ->willReturn('cleanedValue');

        $client = $this->createMock(Client::class);
        $client->expects($this->exactly(6))
            ->method('getApi')
            ->willReturnMap(
                [
                    ['project', $getIdByNameApi],
                    ['issue_category', $issueCategoryGetIdByNameApi],
                    ['issue_status', $getIdByNameApi],
                    ['tracker', $getIdByNameApi],
                    ['user', $getIdByUsernameApi],
                ]
            );

        $client->expects($this->once())
            ->method('requestPut')
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
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->update(5, $parameters));
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
        $response = 'API Response';

        // Create the used mock objects
        $issueStatusApi = $this->createMock('Redmine\Api\Project');
        $issueStatusApi->expects($this->once())
            ->method('getIdByName')
            ->willReturn(123);

        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('getApi')
            ->with('issue_status')
            ->willReturn($issueStatusApi);

        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<id>5</id>'),
                    $this->stringContains('<status_id>123</status_id>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->setIssueStatus(5, 'Status Name'));
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
        $response = 'API Response';

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringEndsWith('</issue>'."\n"),
                    $this->stringContains('<id>5</id>'),
                    $this->stringContains('<notes>Note content</notes>')
                )
            )
            ->willReturn(true);
        $client->expects($this->exactly(1))
            ->method('getLastResponseBody')
            ->willReturn($response);

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $this->assertSame($response, $api->addNoteToIssue(5, 'Note content'));
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
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
            'subject' => 'Issue subject with some xml entities: & < > " \' ',
            'description' => 'Description with some xml entities: & < > " \' ',
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
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPost')
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
                    $this->stringContains('<subject>Issue subject with some xml entities: &amp; &lt; &gt; " \' </subject>'),
                    $this->stringContains('<description>Description with some xml entities: &amp; &lt; &gt; " \' </description>')
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->create($parameters);
    }

    /**
     * Test assign an user to an issue.
     *
     * @test
     */
    public function testAssignUserToAnIssue()
    {
        // Test values
        $parameters = [
            'assigned_to_id' => 5,
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringContains('<assigned_to_id>5</assigned_to_id>'),
                    $this->stringEndsWith('</issue>'."\n"),
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->update(5, $parameters);
    }

    /**
     * Test unassign an user from an issue.
     *
     * @test
     */
    public function testUnassignUserFromAnIssue()
    {
        // Test values
        $parameters = [
            'assigned_to_id' => '',
        ];

        // Create the used mock objects
        $client = $this->createMock(Client::class);
        $client->expects($this->once())
            ->method('requestPut')
            ->with(
                '/issues/5.xml',
                $this->logicalAnd(
                    $this->stringStartsWith('<?xml version="1.0"?>'."\n".'<issue>'),
                    $this->stringContains('<assigned_to_id></assigned_to_id>'),
                    $this->stringEndsWith('</issue>'."\n"),
                )
            );

        // Create the object under test
        $api = new Issue($client);

        // Perform the tests
        $api->update(5, $parameters);
    }
}

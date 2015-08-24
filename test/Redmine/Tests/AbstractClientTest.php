<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient;
use Redmine\Client as Client;

class AbstractClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function shouldPassApiKeyToContructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function shouldPassUsernameAndPasswordToContructor()
    {
        $client = new Client('http://test.local', 'username', 'pwd');

        $this->assertInstanceOf('Redmine\Client', $client);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldNotGetApiInstance()
    {
        $client = new Client('http://test.local', 'asdf');
        $client->api('do_not_exist');
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetUrlReturnsValueFromConstructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame('http://test.local', $client->getUrl());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetPortReturnsPortFromConstructorHttpUrl()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame(80, $client->getPort());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetPortReturnsPortFromConstructorUrlHttps()
    {
        $client = new Client('https://test.local', 'asdf');

        $this->assertSame(443, $client->getPort());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetPortReturnsPortFromConstructorUrlWithPort()
    {
        $client = new Client('http://test.local:8080', 'asdf');

        $this->assertSame(8080, $client->getPort());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetPortReturnsSetPort()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame($client, $client->setPort(28080));
        $this->assertSame(28080, $client->getPort());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetResponseCodeIsInitialNull()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertEquals(0, $client->getResponseCode());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetAndSetCheckSslCertificate()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client->setCheckSslCertificate());
        $this->assertFalse($client->getCheckSslCertificate());
        $this->assertInstanceOf('Redmine\Client', $client->setCheckSslCertificate(true));
        $this->assertTrue($client->getCheckSslCertificate());
        $this->assertInstanceOf('Redmine\Client', $client->setCheckSslCertificate(false));
        $this->assertFalse($client->getCheckSslCertificate());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetAndSetCheckSslHost()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client->setCheckSslHost());
        $this->assertFalse($client->getCheckSslHost());
        $this->assertInstanceOf('Redmine\Client', $client->setCheckSslHost(true));
        $this->assertSame(2, $client->getCheckSslHost());
        $this->assertInstanceOf('Redmine\Client', $client->setCheckSslHost(false));
        $this->assertFalse($client->getCheckSslHost());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetAndSetUseHttpAuth()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client->setUseHttpAuth());
        $this->assertTrue($client->getUseHttpAuth());
        $this->assertInstanceOf('Redmine\Client', $client->setUseHttpAuth(true));
        $this->assertTrue($client->getUseHttpAuth());
        $this->assertInstanceOf('Redmine\Client', $client->setUseHttpAuth(false));
        $this->assertFalse($client->getUseHttpAuth());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetAndSetImpersonateUser()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertNull($client->getImpersonateUser());
        $this->assertInstanceOf('Redmine\Client', $client->setImpersonateUser('Mike'));
        $this->assertSame('Mike', $client->getImpersonateUser());
        $this->assertInstanceOf('Redmine\Client', $client->setImpersonateUser());
        $this->assertNull($client->getImpersonateUser());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetReturnsFalseIfRunRequestReturnsFalse()
    {
        // Create the object under test
        $client = new MockClient('http://test.local', null);
        $client->useOriginalGetMethod = true;
        $client->runRequestReturnValue = false;

        // Perform the tests
        $this->assertSame(false, $client->get('path'));
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetRawJsonFromRunRequest()
    {
        // Create the object under test
        $client = new MockClient('http://test.local', null);
        $client->useOriginalGetMethod = true;
        $client->runRequestReturnValue = '{"foo_bar": 12345}';

        // Perform the tests
        $this->assertSame('{"foo_bar": 12345}', $client->get('path', false));
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetDecodedJsonFromRunRequestByDefault()
    {
        // Create the object under test
        $client = new MockClient('http://test.local', null);
        $client->useOriginalGetMethod = true;
        $client->runRequestReturnValue = '{"foo_bar": 12345}';

        // Perform the tests
        $response = $client->get('path');
        $this->assertSame(12345, $response['foo_bar']);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testDecodeJsonWithValidJson()
    {
        // Test values
        $inputJson = '{"projects":[{"id":1,"name":"Redmine",'
            .'"identifier":"redmine","status":1,'
            .'"created_on":"2007-09-29T10:03:04Z"}],'
            .'"total_count":1,"offset":0,"limit":25}';
        $expectedData = array(
            'projects' => array(
                0 => array(
                    'id' => 1,
                    'name' => 'Redmine',
                    'identifier' => 'redmine',
                    'status' => 1,
                    'created_on' => '2007-09-29T10:03:04Z',
                ),
            ),
            'total_count' => 1,
            'offset' => 0,
            'limit' => 25,
        );

        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        $this->assertSame($expectedData, $client->decode($inputJson));
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testDecodeJsonWithEmptyJson()
    {
        // Test values
        $inputJson = '';
        $expectedData = '';

        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        $this->assertSame($expectedData, $client->decode($inputJson));
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testDecodeJsonWithSyntaxError()
    {
        // Test values
        $invalidJson = '"projects":[{"id":1,"name":"Redmine",'
            .'"identifier":"redmine","status":1,'
            .'"created_on":"2007-09-29T10:03:04Z"}],'
            .'"total_count":1,"offset":0,"limit":25';
        $expectedError = 'Syntax error';

        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        $this->assertSame($expectedError, $client->decode($invalidJson));
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testProcessResponse()
    {
        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        // failed request
        $this->assertFalse(
            $client->processResponse(false, 'application/xml')
        );
        // successfull request
        $this->assertTrue(
            $client->processResponse(true, 'text/html')
        );
        // Text response
        $this->assertSame(
            'response content',
            $client->processResponse('response content', 'text/plain')
        );
        // JSON response
        $this->assertSame(
            '{"api": "redmine"}',
            $client->processResponse('{"api": "redmine"}', 'application/json')
        );
        // XML response
        /* @var $xmlResponse \SimpleXMLElement */
        $xmlResponse = $client->processResponse('<issue/>', 'application/xml');
        $this->assertInstanceOf('SimpleXMLElement', $xmlResponse);
        $this->assertSame('issue', $xmlResponse->getName());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     * @dataProvider getApiClassesProvider
     */
    public function shouldGetApiInstance($apiName, $class)
    {
        $client = new Client('http://test.local', 'asdf');
        $this->assertInstanceOf($class, $client->api($apiName));
    }

    public function getApiClassesProvider()
    {
        return array(
            array('attachment', 'Redmine\Api\Attachment'),
            array('group', 'Redmine\Api\Group'),
            array('custom_fields', 'Redmine\Api\CustomField'),
            array('issue', 'Redmine\Api\Issue'),
            array('issue_category', 'Redmine\Api\IssueCategory'),
            array('issue_priority', 'Redmine\Api\IssuePriority'),
            array('issue_relation', 'Redmine\Api\IssueRelation'),
            array('issue_status', 'Redmine\Api\IssueStatus'),
            array('membership', 'Redmine\Api\Membership'),
            array('news', 'Redmine\Api\News'),
            array('project', 'Redmine\Api\Project'),
            array('query', 'Redmine\Api\Query'),
            array('role', 'Redmine\Api\Role'),
            array('time_entry', 'Redmine\Api\TimeEntry'),
            array('time_entry_activity', 'Redmine\Api\TimeEntryActivity'),
            array('tracker', 'Redmine\Api\Tracker'),
            array('user', 'Redmine\Api\User'),
            array('version', 'Redmine\Api\Version'),
            array('wiki', 'Redmine\Api\Wiki'),
        );
    }
}
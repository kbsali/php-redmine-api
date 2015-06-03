<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient;
use Redmine\CurlClient as Client;

class AbstractClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function shouldPassApiKeyToContructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\CurlClient', $client);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function shouldPassUsernameAndPasswordToContructor()
    {
        $client = new Client('http://test.local', 'username', 'pwd');

        $this->assertInstanceOf('Redmine\CurlClient', $client);
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

        $this->assertInstanceOf('Redmine\CurlClient', $client->setCheckSslCertificate());
        $this->assertFalse($client->getCheckSslCertificate());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setCheckSslCertificate(true));
        $this->assertTrue($client->getCheckSslCertificate());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setCheckSslCertificate(false));
        $this->assertFalse($client->getCheckSslCertificate());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetAndSetCheckSslHost()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\CurlClient', $client->setCheckSslHost());
        $this->assertFalse($client->getCheckSslHost());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setCheckSslHost(true));
        $this->assertSame(2, $client->getCheckSslHost());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setCheckSslHost(false));
        $this->assertFalse($client->getCheckSslHost());
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testGetAndSetUseHttpAuth()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\CurlClient', $client->setUseHttpAuth());
        $this->assertTrue($client->getUseHttpAuth());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setUseHttpAuth(true));
        $this->assertTrue($client->getUseHttpAuth());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setUseHttpAuth(false));
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
        $this->assertInstanceOf('Redmine\CurlClient', $client->setImpersonateUser('Mike'));
        $this->assertSame('Mike', $client->getImpersonateUser());
        $this->assertInstanceOf('Redmine\CurlClient', $client->setImpersonateUser());
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
    public function testPrepareJsonPostRequestWithHttpUsername()
    {
        // Create the object under test
        $client = new Client('http://test.local', 'USER_API-KEY159');
        $client->setPort(8080);

        // Perform the tests
        $data = array(1 => 'post_1', '25' => 'post_25');
        $client->prepareRequest('/issues.json', 'POST', $data);
        $curlOptions = $client->getCurlOptions();
        $this->assertRegExp('/USER_API-KEY159\:[0-9]*/', $curlOptions[CURLOPT_USERPWD]);
        $this->assertSame(CURLAUTH_BASIC, $curlOptions[CURLOPT_HTTPAUTH]);
        $this->assertSame('http://test.local/issues.json', $curlOptions[CURLOPT_URL]);
        $this->assertSame(0, $curlOptions[CURLOPT_VERBOSE]);
        $this->assertSame(0, $curlOptions[CURLOPT_HEADER]);
        $this->assertSame(1, $curlOptions[CURLOPT_RETURNTRANSFER]);
        $this->assertSame(8080, $curlOptions[CURLOPT_PORT]);
        $this->assertContains('Expect: ', $curlOptions[CURLOPT_HTTPHEADER]);
        $this->assertContains(
            'Content-Type: application/json',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertContains(
            'X-Redmine-API-Key: USER_API-KEY159',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertSame(1, $curlOptions[CURLOPT_POST]);
        $this->assertSame($data, $curlOptions[CURLOPT_POSTFIELDS]);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testPrepareXmlPutRequestWithHttpUsernameAndPassword()
    {
        // Create the object under test
        $client = new Client('http://test.local', 'username', 'secret');

        // Perform the tests
        $data = array(1 => 'post_1', '25' => 'post_25');
        $client->prepareRequest('/issues.xml', 'PUT', $data);
        $curlOptions = $client->getCurlOptions();
        $this->assertRegExp('/username\:secret/m', $curlOptions[CURLOPT_USERPWD]);
        $this->assertSame(CURLAUTH_BASIC, $curlOptions[CURLOPT_HTTPAUTH]);
        $this->assertSame('http://test.local/issues.xml', $curlOptions[CURLOPT_URL]);
        $this->assertSame(0, $curlOptions[CURLOPT_VERBOSE]);
        $this->assertSame(0, $curlOptions[CURLOPT_HEADER]);
        $this->assertSame(1, $curlOptions[CURLOPT_RETURNTRANSFER]);
        $this->assertSame(80, $curlOptions[CURLOPT_PORT]);
        $this->assertContains('Expect: ', $curlOptions[CURLOPT_HTTPHEADER]);
        $this->assertContains(
            'Content-Type: text/xml',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertNotContains(
            'X-Redmine-API-Key: username',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertNotContains(
            'X-Redmine-API-Key: secret',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertSame('PUT', $curlOptions[CURLOPT_CUSTOMREQUEST]);
        $this->assertSame($data, $curlOptions[CURLOPT_POSTFIELDS]);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testPrepareDeleteUploadRequestWithSslAndImpersonateUser()
    {
        // Create the object under test
        $client = new Client('https://test.local', 'USER_API-KEY159');
        $client->setImpersonateUser('test_user');
        $client->setUseHttpAuth(false);
        $client->setCheckSslCertificate(true);
        $client->setCheckSslHost(true);

        // Perform the tests
        $data = array(1 => 'post_1', '25' => 'post_25');
        $client->prepareRequest('/uploads.xml', 'DELETE', $data);
        $curlOptions = $client->getCurlOptions();
        $this->assertArrayNotHasKey(CURLOPT_USERPWD, $curlOptions);
        $this->assertArrayNotHasKey(CURLOPT_HTTPAUTH, $curlOptions);
        $this->assertSame('https://test.local/uploads.xml', $curlOptions[CURLOPT_URL]);
        $this->assertSame(0, $curlOptions[CURLOPT_VERBOSE]);
        $this->assertSame(0, $curlOptions[CURLOPT_HEADER]);
        $this->assertSame(1, $curlOptions[CURLOPT_RETURNTRANSFER]);
        $this->assertSame(443, $curlOptions[CURLOPT_PORT]);
        $this->assertSame(1, $curlOptions[CURLOPT_SSL_VERIFYPEER]);
        $this->assertSame(2, $curlOptions[CURLOPT_SSL_VERIFYHOST]);
        $this->assertContains('Expect: ', $curlOptions[CURLOPT_HTTPHEADER]);
        $this->assertContains(
            'Content-Type: application/octet-stream',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertContains(
            'X-Redmine-Switch-User: test_user',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertContains(
            'X-Redmine-API-Key: USER_API-KEY159',
            $curlOptions[CURLOPT_HTTPHEADER]
        );
        $this->assertSame('DELETE', $curlOptions[CURLOPT_CUSTOMREQUEST]);
    }

    /**
     * @covers Redmine\AbstractClient
     * @test
     */
    public function testPrepareGetIssuesRequest()
    {
        // Create the object under test
        $client = new Client('https://test.local', 'USER_API-KEY159');

        // Perform the tests
        $client->prepareRequest('/issues.json', 'GET');
        $curlOptions = $client->getCurlOptions();
        $this->assertSame('https://test.local/issues.json', $curlOptions[CURLOPT_URL]);
        $this->assertSame(0, $curlOptions[CURLOPT_VERBOSE]);
        $this->assertSame(0, $curlOptions[CURLOPT_HEADER]);
        $this->assertSame(1, $curlOptions[CURLOPT_RETURNTRANSFER]);
        $this->assertArrayNotHasKey(CURLOPT_POST, $curlOptions);
        $this->assertArrayNotHasKey(CURLOPT_POSTFIELDS, $curlOptions);
        $this->assertArrayNotHasKey(CURLOPT_CUSTOMREQUEST, $curlOptions);
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
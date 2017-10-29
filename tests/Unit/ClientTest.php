<?php

namespace Redmine\Tests\Unit;

use Redmine\Client;
use Redmine\Tests\Fixtures\MockClient;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers \Redmine\Client
     * @test
     */
    public function shouldPassApiKeyToConstructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client);
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function shouldPassUsernameAndPasswordToConstructor()
    {
        $client = new Client('http://test.local', 'username', 'pwd');

        $this->assertInstanceOf('Redmine\Client', $client);
    }

    /**
     * @covers \Redmine\Client
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function shouldNotGetApiInstance()
    {
        $client = new Client('http://test.local', 'asdf');
        $client->do_not_exist;
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testGetUrlReturnsValueFromConstructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame('http://test.local', $client->getUrl());
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testGetPortReturnsPortFromConstructorHttpUrl()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame(80, $client->getPort());
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testGetPortReturnsPortFromConstructorUrlHttps()
    {
        $client = new Client('https://test.local', 'asdf');

        $this->assertSame(443, $client->getPort());
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testGetPortReturnsPortFromConstructorUrlWithPort()
    {
        $client = new Client('http://test.local:8080', 'asdf');

        $this->assertSame(8080, $client->getPort());
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testGetPortReturnsSetPort()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame($client, $client->setPort(28080));
        $this->assertSame(28080, $client->getPort());
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testGetResponseCodeIsInitialNull()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertEquals(0, $client->getResponseCode());
    }

    /**
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
     * @test
     */
    public function testGetAndSetSSlVersion()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client->setSslVersion());
        $this->assertSame(0, $client->getSslVersion());
        $this->assertInstanceOf('Redmine\Client', $client->setSslVersion(6));
        $this->assertSame(6, $client->getSslVersion());
    }

    /**
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
     * @test
     */
    public function testDecodeJsonWithValidJson()
    {
        // Test values
        $inputJson = '{"projects":[{"id":1,"name":"Redmine",'
            .'"identifier":"redmine","status":1,'
            .'"created_on":"2007-09-29T10:03:04Z"}],'
            .'"total_count":1,"offset":0,"limit":25}';
        $expectedData = [
            'projects' => [
                0 => [
                    'id' => 1,
                    'name' => 'Redmine',
                    'identifier' => 'redmine',
                    'status' => 1,
                    'created_on' => '2007-09-29T10:03:04Z',
                ],
            ],
            'total_count' => 1,
            'offset' => 0,
            'limit' => 25,
        ];

        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        $this->assertSame($expectedData, $client->decode($inputJson));
    }

    /**
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
     * @test
     */
    public function testGetAndSetCurlOptions()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame([], $client->getCurlOptions());
        $this->assertInstanceOf(
            'Redmine\Client',
            $client->setCurlOption(15, 'value')
        );
        $this->assertSame([15 => 'value'], $client->getCurlOptions());
    }

    /**
     * @covers \Redmine\Client
     * @test
     */
    public function testPrepareJsonPostRequestWithHttpUsername()
    {
        // Create the object under test
        $client = new Client('http://test.local', 'USER_API-KEY159');
        $client->setPort(8080);

        // Perform the tests
        $data = [1 => 'post_1', '25' => 'post_25'];
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
     * @covers \Redmine\Client
     * @test
     */
    public function testPrepareXmlPutRequestWithHttpUsernameAndPassword()
    {
        // Create the object under test
        $client = new Client('http://test.local', 'username', 'secret');

        // Perform the tests
        $data = [1 => 'post_1', '25' => 'post_25'];
        $client->setCurlOption(CURLOPT_PROXY, 'PROXYURL:PORT');
        $client->setCustomHost('https://test.com');
        $client->prepareRequest('/issues.xml', 'PUT', $data);

        $curlOptions = $client->getCurlOptions();

        $this->assertRegExp('/username\:secret/m', $curlOptions[CURLOPT_USERPWD]);
        $this->assertSame('PROXYURL:PORT', $curlOptions[CURLOPT_PROXY]);
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
        $this->assertContains(
            'Host: https://test.com',
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
     * @covers \Redmine\Client
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
        $data = [1 => 'post_1', '25' => 'post_25'];
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
     * @covers \Redmine\Client
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
     * @covers \Redmine\Client
     * @test
     */
    public function testProcessCurlResponse()
    {
        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        // failed request
        $this->assertFalse(
            $client->processCurlResponse(false, 'application/xml')
        );
        // successful request
        $this->assertTrue(
            $client->processCurlResponse(true, 'text/html')
        );
        // Text response
        $this->assertSame(
            'response content',
            $client->processCurlResponse('response content', 'text/plain')
        );
        // JSON response
        $this->assertSame(
            '{"api": "redmine"}',
            $client->processCurlResponse('{"api": "redmine"}', 'application/json')
        );
        // XML response
        /* @var $xmlResponse \SimpleXMLElement */
        $xmlResponse = $client->processCurlResponse('<issue/>', 'application/xml');
        $this->assertInstanceOf('\SimpleXMLElement', $xmlResponse);
        $this->assertSame('issue', $xmlResponse->getName());
    }

    /**
     * @covers \Redmine\Client
     * @test
     *
     * @param string $apiName
     * @param string $class
     * @dataProvider getApiClassesProvider
     */
    public function shouldGetApiInstance($apiName, $class)
    {
        $client = new Client('http://test.local', 'asdf');
        $this->assertInstanceOf($class, $client->api($apiName));
    }

    public function getApiClassesProvider()
    {
        return [
            ['attachment', 'Redmine\Api\Attachment'],
            ['group', 'Redmine\Api\Group'],
            ['custom_fields', 'Redmine\Api\CustomField'],
            ['issue', 'Redmine\Api\Issue'],
            ['issue_category', 'Redmine\Api\IssueCategory'],
            ['issue_priority', 'Redmine\Api\IssuePriority'],
            ['issue_relation', 'Redmine\Api\IssueRelation'],
            ['issue_status', 'Redmine\Api\IssueStatus'],
            ['membership', 'Redmine\Api\Membership'],
            ['news', 'Redmine\Api\News'],
            ['project', 'Redmine\Api\Project'],
            ['query', 'Redmine\Api\Query'],
            ['role', 'Redmine\Api\Role'],
            ['time_entry', 'Redmine\Api\TimeEntry'],
            ['time_entry_activity', 'Redmine\Api\TimeEntryActivity'],
            ['tracker', 'Redmine\Api\Tracker'],
            ['user', 'Redmine\Api\User'],
            ['version', 'Redmine\Api\Version'],
            ['wiki', 'Redmine\Api\Wiki'],
        ];
    }
}

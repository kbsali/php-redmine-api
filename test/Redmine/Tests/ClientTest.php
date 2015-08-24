<?php

namespace Redmine\Tests;

use Redmine\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Redmine\Client
     * @test
     */
    public function testGetAndSetCurlOptions()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame(array(), $client->getCurlOptions());
        $this->assertInstanceOf(
            'Redmine\Client',
            $client->setCurlOption(15, 'value')
        );
        $this->assertSame(array(15 => 'value'), $client->getCurlOptions());
    }


    /**
     * @covers Redmine\Client
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
     * @covers Redmine\Client
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
     * @covers Redmine\Client
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
     * @covers Redmine\Client
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

}

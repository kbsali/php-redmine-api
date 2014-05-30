<?php

namespace Redmine\Tests;

use Redmine\Client;
use Redmine\Exception\InvalidArgumentException;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldPassApiKeyToContructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertInstanceOf('Redmine\Client', $client);
    }

    /**
     * @test
     */
    public function shouldPassUsernameAndPasswordToContructor()
    {
        $client = new Client('http://test.local', 'username', 'pwd');

        $this->assertInstanceOf('Redmine\Client', $client);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function shouldNotGetApiInstance()
    {
        $client = new Client('http://test.local', 'asdf');
        $client->api('do_not_exist');
    }

    /**
     * @test
     */
    public function testGetUrlReturnsValueFromConstructor()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame('http://test.local', $client->getUrl());
    }

    /**
     * @test
     */
    public function testGetPortReturnsPortFromConstructorHttpUrl()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame(80, $client->getPort());
    }

    /**
     * @test
     */
    public function testGetPortReturnsPortFromConstructorUrlHttps()
    {
        $client = new Client('https://test.local', 'asdf');

        $this->assertSame(443, $client->getPort());
    }

    /**
     * @test
     */
    public function testGetPortReturnsPortFromConstructorUrlWithPort()
    {
        $client = new Client('http://test.local:8080', 'asdf');

        $this->assertSame(8080, $client->getPort());
    }

    /**
     * @test
     */
    public function testGetPortReturnsSetPort()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertSame($client, $client->setPort(28080));
        $this->assertSame(28080, $client->getPort());
    }

    /**
     * @test
     */
    public function testGetResponseCodeIsInitialNull()
    {
        $client = new Client('http://test.local', 'asdf');

        $this->assertNull($client->getResponseCode());
    }

    /**
     * @test
     */
    public function testGetApikeyHeaderNameReturnsSetApikeyHeaderName()
    {
        // Test values
        $headerName = 'X-Header-Redmine-API-Auth';
        $otherHeaderName = 'X-Header-API-Auth';

        $client = new Client('http://test.local', 'asdf');

        $this->assertSame($client, $client->setApikeyHeaderName($headerName));
        $this->assertSame($headerName, $client->getApikeyHeaderName());

        $this->assertSame($client, $client->setApikeyHeaderName(null));
        $this->assertSame($headerName, $client->getApikeyHeaderName());

        $this->assertSame($client, $client->setApikeyHeaderName($otherHeaderName));
        $this->assertSame($otherHeaderName, $client->getApikeyHeaderName());
    }

    /**
     * @test
     */
    public function testDecodeJsonWithValidJson()
    {
        // Test values
        $inputJson = '{"projects":[{"id":1,"name":"Redmine",'
            . '"identifier":"redmine","status":1,'
            . '"created_on":"2007-09-29T10:03:04Z"}],'
            . '"total_count":1,"offset":0,"limit":25}';
        $expectedData = array(
            'projects' => array(
                0 => array(
                    'id' => 1,
                    'name' => 'Redmine',
                    'identifier' => 'redmine',
                    'status' => 1,
                    'created_on' => '2007-09-29T10:03:04Z',
                )
            ),
            'total_count' => 1,
            'offset' => 0,
            'limit' => 25
        );

        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        $this->assertSame($expectedData, $client->decode($inputJson));
    }

    /**
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
     * @test
     */
    public function testDecodeJsonWithSyntaxError()
    {
        // Test values
        $invalidJson = '"projects":[{"id":1,"name":"Redmine",'
            . '"identifier":"redmine","status":1,'
            . '"created_on":"2007-09-29T10:03:04Z"}],'
            . '"total_count":1,"offset":0,"limit":25';
        $expectedError = 'Syntax error';

        // Create the object under test
        $client = new Client('http://test.local', 'asdf');

        // Perform the tests
        $this->assertSame($expectedError, $client->decode($invalidJson));
    }

    /**
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

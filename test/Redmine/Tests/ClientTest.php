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

<?php

namespace Redmine\Tests;

use Redmine\TestUrlClient;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setup()
    {
        $this->client = new TestUrlClient('http://test.local', 'asdf');
    }

    public function testAttachment()
    {
        $res = $this->client->api('attachment')->show(1);
        $this->assertEquals($res, array('path' => '/attachments/1.json', 'method' => 'GET'));

        $res = $this->client->api('attachment')->upload('asdf');
        $this->assertEquals($res, array('path' => '/uploads.json', 'method' => 'POST'));
    }

    public function testCustomFields()
    {
        $res = $this->client->api('custom_fields')->all();
        $this->assertEquals($res, array('path' => '/custom_fields.json', 'method' => 'GET'));
    }

    public function testGroup()
    {
        $res = $this->client->api('group')->create(array(
            'name' => 'asdf'
        ));
        $this->assertEquals($res, array('path' => '/groups.xml', 'method' => 'POST'));

        $res = $this->client->api('group')->all();
        $this->assertEquals($res, array('path' => '/groups.json', 'method' => 'GET'));

        $res = $this->client->api('group')->show(1);
        $this->assertEquals($res, array('path' => '/groups/1.json?', 'method' => 'GET'));

        $res = $this->client->api('group')->remove(1);
        $this->assertEquals($res, array('path' => '/groups/1.xml', 'method' => 'DELETE'));

        $res = $this->client->api('group')->addUser(1, 1);
        $this->assertEquals($res, array('path' => '/groups/1/user/users.xml', 'method' => 'POST'));

        $res = $this->client->api('group')->removeUser(1, 1);
        $this->assertEquals($res, array('path' => '/groups/1/user/1.xml', 'method' => 'DELETE'));
    }

    public function testIssue()
    {
        $res = $this->client->api('issue')->create(array(
            'name' => 'asdf'
        ));
        $this->assertEquals($res, array('path' => '/issues.xml', 'method' => 'POST'));

        $res = $this->client->api('issue')->update(1, array(
            'name' => 'asdf'
        ));
        $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('issue')->all();
        $this->assertEquals($res, array('path' => '/issues.json', 'method' => 'GET'));

        $res = $this->client->api('issue')->show(1);
        $this->assertEquals($res, array('path' => '/issues/1.json?', 'method' => 'GET'));

        $res = $this->client->api('issue')->remove(1);
        $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'DELETE'));

        // $res = $this->client->api('issue')->setIssueStatus(1, 'asdf');
        // $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'DELETE'));

        $res = $this->client->api('issue')->addNoteToIssue(1, 'asdf');
        $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('issue')->attach(1, array('asdf'));
        $this->assertEquals($res, array('path' => '/issues/1.json', 'method' => 'PUT'));
    }

    public function testIssueCategory()
    {
        $res = $this->client->api('issue_category')->create('testProject', array(
            'name' => 'asdf'
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/issue_categories.xml', 'method' => 'POST'));

        $res = $this->client->api('issue_category')->update(1, array(
            'name' => 'asdf'
        ));
        $this->assertEquals($res, array('path' => '/issue_categories/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('issue_category')->all('testProject');
        $this->assertEquals($res, array('path' => '/projects/testProject/issue_categories.json', 'method' => 'GET'));

        $res = $this->client->api('issue_category')->show(1);
        $this->assertEquals($res, array('path' => '/issue_categories/1.json', 'method' => 'GET'));

        $res = $this->client->api('issue_category')->remove(1);
        $this->assertEquals($res, array('path' => '/issue_categories/1.xml?', 'method' => 'DELETE'));
    }

    public function testIssuePriority()
    {
        $res = $this->client->api('issue_priority')->all();
        $this->assertEquals($res, array('path' => '/enumerations/issue_priorities.json', 'method' => 'GET'));
    }

    public function testIssueRelation()
    {
        $res = $this->client->api('issue_relation')->all(1);
        $this->assertEquals($res, array('path' => '/issues/1/relations.json', 'method' => 'GET'));

        // $res = $this->client->api('issue_relation')->show(1);
        // $this->assertEquals($res, array('path' => '/relations/1.json', 'method' => 'GET'));

        $res = $this->client->api('issue_relation')->remove(1);
        $this->assertEquals($res, array('path' => '/relations/1.xml', 'method' => 'DELETE'));
    }

    public function testIssueStatus()
    {
        $res = $this->client->api('issue_status')->all();
        $this->assertEquals($res, array('path' => '/issue_statuses.json', 'method' => 'GET'));
    }

    public function testMembership()
    {
        $res = $this->client->api('membership')->create('testProject', array(
            'user_id' => 1,
            'role_ids' => [1],
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/memberships.xml', 'method' => 'POST'));

        $res = $this->client->api('membership')->update(1, array(
            'user_id' => 1,
            'role_ids' => [1],
        ));
        $this->assertEquals($res, array('path' => '/memberships/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('membership')->all('testProject');
        $this->assertEquals($res, array('path' => '/projects/testProject/memberships.json', 'method' => 'GET'));

        $res = $this->client->api('membership')->remove(1);
        $this->assertEquals($res, array('path' => '/memberships/1.xml', 'method' => 'DELETE'));
    }

    public function testNews()
    {
        $res = $this->client->api('news')->all();
        $this->assertEquals($res, array('path' => '/news.json', 'method' => 'GET'));

        $res = $this->client->api('news')->all('testProject');
        $this->assertEquals($res, array('path' => '/projects/testProject/news.json', 'method' => 'GET'));
    }

    public function testProject()
    {
        $res = $this->client->api('project')->create(array(
            'name' => 'asdf',
            'identifier' => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects.xml', 'method' => 'POST'));

        $res = $this->client->api('project')->update(1, array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('project')->all();
        $this->assertEquals($res, array('path' => '/projects.json', 'method' => 'GET'));

        $res = $this->client->api('project')->show(1);
        $this->assertEquals($res, array('path' => '/projects/1.json?include=trackers,issue_categories,attachments,relations', 'method' => 'GET'));

        $res = $this->client->api('project')->remove(1);
        $this->assertEquals($res, array('path' => '/projects/1.xml', 'method' => 'DELETE'));
    }

    public function testQuery()
    {
        $res = $this->client->api('query')->all();
        $this->assertEquals($res, array('path' => '/queries.json', 'method' => 'GET'));
    }

    public function testRole()
    {
        $res = $this->client->api('role')->all();
        $this->assertEquals($res, array('path' => '/roles.json', 'method' => 'GET'));

        $res = $this->client->api('role')->show(1);
        $this->assertEquals($res, array('path' => '/roles/1.json', 'method' => 'GET'));
    }

    public function testTimeEntry()
    {
        $res = $this->client->api('time_entry')->create(array(
            'issue_id' => 1,
            'hours' => 12,
        ));
        $this->assertEquals($res, array('path' => '/time_entries.xml', 'method' => 'POST'));

        $res = $this->client->api('time_entry')->update(1, array());
        $this->assertEquals($res, array('path' => '/time_entries/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('time_entry')->all();
        $this->assertEquals($res, array('path' => '/time_entries.json', 'method' => 'GET'));

        $res = $this->client->api('time_entry')->show(1);
        $this->assertEquals($res, array('path' => '/time_entries/1.json', 'method' => 'GET'));

        $res = $this->client->api('time_entry')->remove(1);
        $this->assertEquals($res, array('path' => '/time_entries/1.xml', 'method' => 'DELETE'));
    }

    public function testTimeEntryActivity()
    {
        $res = $this->client->api('time_entry_activity')->all();
        $this->assertEquals($res, array('path' => '/enumerations/time_entry_activities.json', 'method' => 'GET'));
    }

    public function testTracker()
    {
        $res = $this->client->api('tracker')->all();
        $this->assertEquals($res, array('path' => '/trackers.json', 'method' => 'GET'));
    }

    public function testUser()
    {
        $res = $this->client->api('user')->create(array(
            'login' => 'asdf',
            'lastname' => 'asdf',
            'firstname' => 'asdf',
            'mail' => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/users.xml', 'method' => 'POST'));

        $res = $this->client->api('user')->update(1, array());
        $this->assertEquals($res, array('path' => '/users/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('user')->all();
        $this->assertEquals($res, array('path' => '/users.json', 'method' => 'GET'));

        $res = $this->client->api('user')->show(1);
        $this->assertEquals($res, array('path' => '/users/1.json?include=memberships,groups', 'method' => 'GET'));

        $res = $this->client->api('user')->remove(1);
        $this->assertEquals($res, array('path' => '/users/1.xml', 'method' => 'DELETE'));
    }

    public function testVersion()
    {
        $res = $this->client->api('version')->create('testProject', array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/versions.xml', 'method' => 'POST'));

        $res = $this->client->api('version')->update(1, array());
        $this->assertEquals($res, array('path' => '/versions/1.xml', 'method' => 'PUT'));

        $res = $this->client->api('version')->all('testProject');
        $this->assertEquals($res, array('path' => '/projects/testProject/versions.json', 'method' => 'GET'));

        $res = $this->client->api('version')->show(1);
        $this->assertEquals($res, array('path' => '/versions/1.json', 'method' => 'GET'));

        $res = $this->client->api('version')->remove(1);
        $this->assertEquals($res, array('path' => '/versions/1.xml', 'method' => 'DELETE'));
    }

    public function testWiki()
    {
        $res = $this->client->api('wiki')->create('testProject', 'about', array(
            'text'     => 'asdf',
            'comments' => 'asdf',
            'version'  => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.xml', 'method' => 'PUT'));

        $res = $this->client->api('wiki')->update('testProject', 'about', array(
            'text'     => 'asdf',
            'comments' => 'asdf',
            'version'  => 'asdf',
        ));
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.xml', 'method' => 'PUT'));

        $res = $this->client->api('wiki')->all('testProject');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/index.json', 'method' => 'GET'));

        $res = $this->client->api('wiki')->show('testProject', 'about');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.json', 'method' => 'GET'));

        $res = $this->client->api('wiki')->show('testProject', 'about', 'v1');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about/v1.json', 'method' => 'GET'));

        $res = $this->client->api('wiki')->remove('testProject', 'about');
        $this->assertEquals($res, array('path' => '/projects/testProject/wiki/about.xml', 'method' => 'DELETE'));
    }
}

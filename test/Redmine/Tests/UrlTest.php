<?php

namespace Redmine\Tests;

use Redmine\Fixtures\MockClient as TestUrlClient;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setup()
    {
        $this->client = new TestUrlClient('http://test.local', 'asdf');
    }

    public function testAttachment()
    {
        $res = $this->client->attachment->show(1);
        $this->assertEquals($res['path'], '/attachments/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->attachment->upload('asdf');
        $this->assertEquals($res['path'], '/uploads.json');
        $this->assertEquals($res['method'], 'POST');
    }

    public function testCustomFields()
    {
        $res = $this->client->custom_fields->all();
        $this->assertEquals($res['path'], '/custom_fields.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testGroup()
    {
        $res = $this->client->group->create(array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/groups.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->group->all();
        $this->assertEquals($res['path'], '/groups.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->group->show(1);
        $this->assertEquals($res['path'], '/groups/1.json?');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->group->remove(1);
        $this->assertEquals($res['path'], '/groups/1.xml');
        $this->assertEquals($res['method'], 'DELETE');

        $res = $this->client->group->addUser(1, 1);
        $this->assertEquals($res['path'], '/groups/1/users.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->group->removeUser(1, 1);
        $this->assertEquals($res['path'], '/groups/1/users/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testIssue()
    {
        $res = $this->client->issue->create(array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/issues.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->issue->update(1, array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/issues/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->issue->all();
        $this->assertEquals($res['path'], '/issues.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->issue->show(1);
        $this->assertEquals($res['path'], '/issues/1.json?');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->issue->remove(1);
        $this->assertEquals($res['path'], '/issues/1.xml');
        $this->assertEquals($res['method'], 'DELETE');

        // $res = $this->client->issue->setIssueStatus(1, 'asdf');
        // $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'DELETE'));

        $res = $this->client->issue->addNoteToIssue(1, 'asdf');
        $this->assertEquals($res['path'], '/issues/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->issue->attach(1, array('asdf'));
        $this->assertEquals($res['path'], '/issues/1.json');
        $this->assertEquals($res['method'], 'PUT');
    }

    public function testIssueCategory()
    {
        $res = $this->client->issue_category->create('testProject', array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/projects/testProject/issue_categories.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->issue_category->update(1, array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/issue_categories/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->issue_category->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/issue_categories.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->issue_category->show(1);
        $this->assertEquals($res['path'], '/issue_categories/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->issue_category->remove(1);
        $this->assertEquals($res['path'], '/issue_categories/1.xml?');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testIssuePriority()
    {
        $res = $this->client->issue_priority->all();
        $this->assertEquals($res['path'], '/enumerations/issue_priorities.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testIssueRelation()
    {
        $res = $this->client->issue_relation->all(1);
        $this->assertEquals($res['path'], '/issues/1/relations.json');
        $this->assertEquals($res['method'], 'GET');

        // $res = $this->client->issue_relation->show(1);
        // $this->assertEquals($res, array('path' => '/relations/1.json', 'method' => 'GET'));

        $res = $this->client->issue_relation->remove(1);
        $this->assertEquals($res['path'], '/relations/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testIssueStatus()
    {
        $res = $this->client->issue_status->all();
        $this->assertEquals($res['path'], '/issue_statuses.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testMembership()
    {
        $res = $this->client->membership->create('testProject', array(
            'user_id' => 1,
            'role_ids' => array(1),
        ));
        $this->assertEquals($res['path'], '/projects/testProject/memberships.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->membership->update(1, array(
            'user_id' => 1,
            'role_ids' => array(1),
        ));
        $this->assertEquals($res['path'], '/memberships/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->membership->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/memberships.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->membership->remove(1);
        $this->assertEquals($res['path'], '/memberships/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testNews()
    {
        $res = $this->client->news->all();
        $this->assertEquals($res['path'], '/news.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->news->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/news.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testProject()
    {
        $res = $this->client->project->create(array(
            'name' => 'asdf',
            'identifier' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/projects.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->project->update(1, array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/projects/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->project->all();
        $this->assertEquals($res['path'], '/projects.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->project->show(1);
        $this->assertEquals($res['path'], '/projects/1.json?include=trackers,issue_categories,attachments,relations');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->project->remove(1);
        $this->assertEquals($res['path'], '/projects/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testQuery()
    {
        $res = $this->client->query->all();
        $this->assertEquals($res['path'], '/queries.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testRole()
    {
        $res = $this->client->role->all();
        $this->assertEquals($res['path'], '/roles.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->role->show(1);
        $this->assertEquals($res['path'], '/roles/1.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testTimeEntry()
    {
        $res = $this->client->time_entry->create(array(
            'issue_id' => 1,
            'hours' => 12,
        ));
        $this->assertEquals($res['path'], '/time_entries.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->time_entry->update(1, array());
        $this->assertEquals($res['path'], '/time_entries/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->time_entry->all();
        $this->assertEquals($res['path'], '/time_entries.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->time_entry->show(1);
        $this->assertEquals($res['path'], '/time_entries/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->time_entry->remove(1);
        $this->assertEquals($res['path'], '/time_entries/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testTimeEntryActivity()
    {
        $res = $this->client->time_entry_activity->all();
        $this->assertEquals($res['path'], '/enumerations/time_entry_activities.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testTracker()
    {
        $res = $this->client->tracker->all();
        $this->assertEquals($res['path'], '/trackers.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testUser()
    {
        $res = $this->client->user->create(array(
            'login' => 'asdf',
            'lastname' => 'asdf',
            'firstname' => 'asdf',
            'mail' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/users.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->user->update(1, array());
        $this->assertEquals($res['path'], '/users/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->user->all();
        $this->assertEquals($res['path'], '/users.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->user->show(1);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->user->show(1, array('include' => array('memberships', 'groups')));
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->user->show(1, array('include' => array('memberships', 'groups', 'parameter1')));
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups,parameter1'));
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->user->show(1, array('include' => array('parameter1', 'memberships', 'groups')));
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('parameter1,memberships,groups'));
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->user->remove(1);
        $this->assertEquals($res['path'], '/users/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testVersion()
    {
        $res = $this->client->version->create('testProject', array(
            'name' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/projects/testProject/versions.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $this->client->version->update(1, array());
        $this->assertEquals($res['path'], '/versions/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->version->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/versions.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->version->show(1);
        $this->assertEquals($res['path'], '/versions/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->version->remove(1);
        $this->assertEquals($res['path'], '/versions/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testWiki()
    {
        $res = $this->client->wiki->create('testProject', 'about', array(
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->wiki->update('testProject', 'about', array(
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ));
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $this->client->wiki->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/index.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->wiki->show('testProject', 'about');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.json?include=attachments');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->wiki->show('testProject', 'about', 'v1');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about/v1.json?include=attachments');
        $this->assertEquals($res['method'], 'GET');

        $res = $this->client->wiki->remove('testProject', 'about');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }
}

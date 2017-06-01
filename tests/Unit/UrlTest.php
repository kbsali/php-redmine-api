<?php

namespace Redmine\Tests\Unit;

use Redmine\Tests\Fixtures\MockClient as TestUrlClient;

class UrlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestUrlClient
     */
    private $client;

    public function setup()
    {
        $this->client = new TestUrlClient('http://test.local', 'asdf');
    }

    public function testAttachment()
    {
        $api = $this->client->attachment;
        $res = $api->show(1);
        $this->assertEquals($res['path'], '/attachments/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->upload('asdf');
        $this->assertEquals($res['path'], '/uploads.json');
        $this->assertEquals($res['method'], 'POST');
    }

    public function testCustomFields()
    {
        $api = $this->client->custom_fields;
        $res = $api->all();
        $this->assertEquals($res['path'], '/custom_fields.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testGroup()
    {
        $api = $this->client->group;
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/groups.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->all();
        $this->assertEquals($res['path'], '/groups.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/groups/1.json?');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/groups/1.xml');
        $this->assertEquals($res['method'], 'DELETE');

        $res = $api->addUser(1, 1);
        $this->assertEquals($res['path'], '/groups/1/users.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->removeUser(1, 1);
        $this->assertEquals($res['path'], '/groups/1/users/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testIssue()
    {
        $api = $this->client->issue;
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/issues.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/issues/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all();
        $this->assertEquals($res['path'], '/issues.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/issues/1.json?');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/issues/1.xml');
        $this->assertEquals($res['method'], 'DELETE');

        // $res = $api->setIssueStatus(1, 'asdf');
        // $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'DELETE'));

        $res = $api->addNoteToIssue(1, 'asdf');
        $this->assertEquals($res['path'], '/issues/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->attach(1, ['asdf']);
        $this->assertEquals($res['path'], '/issues/1.json');
        $this->assertEquals($res['method'], 'PUT');
    }

    public function testIssueCategory()
    {
        $api = $this->client->issue_category;
        $res = $api->create('testProject', [
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/projects/testProject/issue_categories.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/issue_categories/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/issue_categories.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/issue_categories/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/issue_categories/1.xml?');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testIssuePriority()
    {
        $api = $this->client->issue_priority;
        $res = $api->all();
        $this->assertEquals($res['path'], '/enumerations/issue_priorities.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testIssueRelation()
    {
        $api = $this->client->issue_relation;
        $res = $api->all(1);
        $this->assertEquals($res['path'], '/issues/1/relations.json');
        $this->assertEquals($res['method'], 'GET');

        // $res = $api->show(1);
        // $this->assertEquals($res, array('path' => '/relations/1.json', 'method' => 'GET'));

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/relations/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testIssueStatus()
    {
        $api = $this->client->issue_status;
        $res = $api->all();
        $this->assertEquals($res['path'], '/issue_statuses.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testMembership()
    {
        $api = $this->client->membership;
        $res = $api->create('testProject', [
            'user_id' => 1,
            'role_ids' => [1],
        ]);
        $this->assertEquals($res['path'], '/projects/testProject/memberships.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, [
            'user_id' => 1,
            'role_ids' => [1],
        ]);
        $this->assertEquals($res['path'], '/memberships/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/memberships.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/memberships/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testNews()
    {
        $api = $this->client->news;
        $res = $api->all();
        $this->assertEquals($res['path'], '/news.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/news.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testProject()
    {
        $api = $this->client->project;
        $res = $api->create([
            'name' => 'asdf',
            'identifier' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/projects.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/projects/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all();
        $this->assertEquals($res['path'], '/projects.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/projects/1.json?include='.urlencode('trackers,issue_categories,attachments,relations'));
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/projects/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testQuery()
    {
        $api = $this->client->query;
        $res = $api->all();
        $this->assertEquals($res['path'], '/queries.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testRole()
    {
        $api = $this->client->role;
        $res = $api->all();
        $this->assertEquals($res['path'], '/roles.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/roles/1.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testTimeEntry()
    {
        $api = $this->client->time_entry;
        $res = $api->create([
            'issue_id' => 1,
            'hours' => 12,
        ]);
        $this->assertEquals($res['path'], '/time_entries.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, []);
        $this->assertEquals($res['path'], '/time_entries/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all();
        $this->assertEquals($res['path'], '/time_entries.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/time_entries/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/time_entries/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testTimeEntryActivity()
    {
        $api = $this->client->time_entry_activity;
        $res = $api->all();
        $this->assertEquals($res['path'], '/enumerations/time_entry_activities.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testTracker()
    {
        $api = $this->client->tracker;
        $res = $api->all();
        $this->assertEquals($res['path'], '/trackers.json');
        $this->assertEquals($res['method'], 'GET');
    }

    public function testUser()
    {
        $api = $this->client->user;
        $res = $api->create([
            'login' => 'asdf',
            'lastname' => 'asdf',
            'firstname' => 'asdf',
            'mail' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/users.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, []);
        $this->assertEquals($res['path'], '/users/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all();
        $this->assertEquals($res['path'], '/users.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1, ['include' => ['memberships', 'groups']]);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1, ['include' => ['memberships', 'groups', 'parameter1']]);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups,parameter1'));
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1, ['include' => ['parameter1', 'memberships', 'groups']]);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('parameter1,memberships,groups'));
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/users/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testVersion()
    {
        $api = $this->client->version;
        $res = $api->create('testProject', [
            'name' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/projects/testProject/versions.xml');
        $this->assertEquals($res['method'], 'POST');

        $res = $api->update(1, []);
        $this->assertEquals($res['path'], '/versions/1.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/versions.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/versions/1.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove(1);
        $this->assertEquals($res['path'], '/versions/1.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }

    public function testWiki()
    {
        $api = $this->client->wiki;
        $res = $api->create('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->update('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.xml');
        $this->assertEquals($res['method'], 'PUT');

        $res = $api->all('testProject');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/index.json');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show('testProject', 'about');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.json?include=attachments');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->show('testProject', 'about', 'v1');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about/v1.json?include=attachments');
        $this->assertEquals($res['method'], 'GET');

        $res = $api->remove('testProject', 'about');
        $this->assertEquals($res['path'], '/projects/testProject/wiki/about.xml');
        $this->assertEquals($res['method'], 'DELETE');
    }
}

<?php

namespace Redmine\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient as TestUrlClient;

class UrlTest extends TestCase
{
    /**
     * @var TestUrlClient
     */
    private $client;

    public function setup(): void
    {
        $this->client = new TestUrlClient('http://test.local', 'asdf');
    }

    public function testAttachment()
    {
        $api = $this->client->attachment;
        $res = $api->show(1);
        $this->assertEquals('/attachments/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->upload('asdf');
        $this->assertEquals('/uploads.json?', $res['path']);
        $this->assertEquals('POST', $res['method']);
    }

    public function testCustomFields()
    {
        $api = $this->client->custom_fields;
        $res = $api->all();
        $this->assertEquals('/custom_fields.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testGroup()
    {
        $api = $this->client->group;
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $this->assertEquals('/groups.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->all();
        $this->assertEquals('/groups.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals('/groups/1.json?', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/groups/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);

        $res = $api->addUser(1, 1);
        $this->assertEquals('/groups/1/users.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->removeUser(1, 1);
        $this->assertEquals('/groups/1/users/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssue()
    {
        $api = $this->client->issue;
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $this->assertEquals('/issues.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $this->assertEquals('/issues/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $this->assertEquals('/issues.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals('/issues/1.json?', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/issues/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);

        // $res = $api->setIssueStatus(1, 'asdf');
        // $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'DELETE'));

        $res = $api->addNoteToIssue(1, 'asdf');
        $this->assertEquals('/issues/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->attach(1, ['asdf']);
        $this->assertEquals('/issues/1.json', $res['path']);
        $this->assertEquals('PUT', $res['method']);
    }

    public function testIssueCategory()
    {
        $api = $this->client->issue_category;
        $res = $api->create('testProject', [
            'name' => 'asdf',
        ]);
        $this->assertEquals('/projects/testProject/issue_categories.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $this->assertEquals('/issue_categories/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $this->assertEquals('/projects/testProject/issue_categories.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals('/issue_categories/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/issue_categories/1.xml?', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssuePriority()
    {
        $api = $this->client->issue_priority;
        $res = $api->all();
        $this->assertEquals('/enumerations/issue_priorities.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testIssueRelation()
    {
        $api = $this->client->issue_relation;
        $res = $api->all(1);
        $this->assertEquals('/issues/1/relations.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        // $res = $api->show(1);
        // $this->assertEquals($res, array('path' => '/relations/1.json', 'method' => 'GET'));

        $res = $api->remove(1);
        $this->assertEquals('/relations/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssueStatus()
    {
        $api = $this->client->issue_status;
        $res = $api->all();
        $this->assertEquals('/issue_statuses.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testMembership()
    {
        $api = $this->client->membership;
        $res = $api->create('testProject', [
            'user_id' => 1,
            'role_ids' => [1],
        ]);
        $this->assertEquals('/projects/testProject/memberships.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'user_id' => 1,
            'role_ids' => [1],
        ]);
        $this->assertEquals('/memberships/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $this->assertEquals('/projects/testProject/memberships.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/memberships/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testNews()
    {
        $api = $this->client->news;
        $res = $api->all();
        $this->assertEquals('/news.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->all('testProject');
        $this->assertEquals('/projects/testProject/news.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testProject()
    {
        $api = $this->client->project;
        $res = $api->create([
            'name' => 'asdf',
            'identifier' => 'asdf',
        ]);
        $this->assertEquals('/projects.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $this->assertEquals('/projects/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $this->assertEquals('/projects.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/projects/1.json?include='.urlencode('trackers,issue_categories,attachments,relations'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/projects/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testQuery()
    {
        $api = $this->client->query;
        $res = $api->all();
        $this->assertEquals('/queries.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testRole()
    {
        $api = $this->client->role;
        $res = $api->all();
        $this->assertEquals('/roles.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals('/roles/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testTimeEntry()
    {
        $api = $this->client->time_entry;
        $res = $api->create([
            'issue_id' => 1,
            'hours' => 12,
        ]);
        $this->assertEquals('/time_entries.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, []);
        $this->assertEquals('/time_entries/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $this->assertEquals('/time_entries.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals('/time_entries/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/time_entries/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testTimeEntryActivity()
    {
        $api = $this->client->time_entry_activity;
        $res = $api->all();
        $this->assertEquals('/enumerations/time_entry_activities.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testTracker()
    {
        $api = $this->client->tracker;
        $res = $api->all();
        $this->assertEquals('/trackers.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
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
        $this->assertEquals('/users.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, []);
        $this->assertEquals('/users/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $this->assertEquals('/users.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1, ['include' => ['memberships', 'groups']]);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1, ['include' => ['memberships', 'groups', 'parameter1']]);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups,parameter1'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1, ['include' => ['parameter1', 'memberships', 'groups']]);
        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('parameter1,memberships,groups'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/users/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testVersion()
    {
        $api = $this->client->version;
        $res = $api->create('testProject', [
            'name' => 'asdf',
        ]);
        $this->assertEquals('/projects/testProject/versions.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, []);
        $this->assertEquals('/versions/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $this->assertEquals('/projects/testProject/versions.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $this->assertEquals('/versions/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $this->assertEquals('/versions/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testWiki()
    {
        $api = $this->client->wiki;
        $res = $api->create('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->update('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $this->assertEquals('/projects/testProject/wiki/index.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show('testProject', 'about');
        $this->assertEquals('/projects/testProject/wiki/about.json?include=attachments', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show('testProject', 'about', 'v1');
        $this->assertEquals('/projects/testProject/wiki/about/v1.json?include=attachments', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove('testProject', 'about');
        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }
}

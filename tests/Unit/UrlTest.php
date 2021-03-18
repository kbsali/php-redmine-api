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
        $api = $this->client->getApi('attachment');
        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/attachments/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->upload('asdf');
        $res = json_decode($res, true);

        $this->assertEquals('/uploads.json?', $res['path']);
        $this->assertEquals('POST', $res['method']);
    }

    public function testCustomFields()
    {
        $api = $this->client->getApi('custom_fields');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/custom_fields.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testGroup()
    {
        $api = $this->client->getApi('group');
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/groups.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/groups.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/groups/1.json?', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/groups/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);

        $res = $api->addUser(1, 1);
        $res = json_decode($res, true);

        $this->assertEquals('/groups/1/users.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->removeUser(1, 1);
        $res = json_decode($res, true);

        $this->assertEquals('/groups/1/users/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssue()
    {
        $api = $this->client->getApi('issue');
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/issues.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/issues/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/issues.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/issues/1.json?', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/issues/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);

        // $res = $api->setIssueStatus(1, 'asdf');
        // $this->assertEquals($res, array('path' => '/issues/1.xml', 'method' => 'DELETE'));

        $res = $api->addNoteToIssue(1, 'asdf');
        $res = json_decode($res, true);

        $this->assertEquals('/issues/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->attach(1, ['asdf']);
        $res = json_decode($res, true);

        $this->assertEquals('/issues/1.json', $res['path']);
        $this->assertEquals('PUT', $res['method']);
    }

    public function testIssueCategory()
    {
        $api = $this->client->getApi('issue_category');
        $res = $api->create('testProject', [
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/issue_categories.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/issue_categories/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/issue_categories.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/issue_categories/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/issue_categories/1.xml?', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssuePriority()
    {
        $api = $this->client->getApi('issue_priority');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/enumerations/issue_priorities.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testIssueRelation()
    {
        $api = $this->client->getApi('issue_relation');
        $res = $api->all(1);
        $res = json_decode($res, true);

        $this->assertEquals('/issues/1/relations.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        // $res = $api->show(1);
        // $this->assertEquals($res, array('path' => '/relations/1.json', 'method' => 'GET'));

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/relations/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssueStatus()
    {
        $api = $this->client->getApi('issue_status');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/issue_statuses.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testMembership()
    {
        $api = $this->client->getApi('membership');
        $res = $api->create('testProject', [
            'user_id' => 1,
            'role_ids' => [1],
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/memberships.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'user_id' => 1,
            'role_ids' => [1],
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/memberships/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/memberships.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/memberships/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testNews()
    {
        $api = $this->client->getApi('news');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/news.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->all('testProject');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/news.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testProject()
    {
        $api = $this->client->getApi('project');
        $res = $api->create([
            'name' => 'asdf',
            'identifier' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, [
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/projects.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals($res['path'], '/projects/1.json?include='.urlencode('trackers,issue_categories,attachments,relations'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testQuery()
    {
        $api = $this->client->getApi('query');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/queries.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testRole()
    {
        $api = $this->client->getApi('role');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/roles.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/roles/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testTimeEntry()
    {
        $api = $this->client->getApi('time_entry');
        $res = $api->create([
            'issue_id' => 1,
            'hours' => 12,
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/time_entries.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, []);
        $res = json_decode($res, true);

        $this->assertEquals('/time_entries/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/time_entries.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/time_entries/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/time_entries/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testTimeEntryActivity()
    {
        $api = $this->client->getApi('time_entry_activity');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/enumerations/time_entry_activities.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testTracker()
    {
        $api = $this->client->getApi('tracker');
        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/trackers.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testUser()
    {
        $api = $this->client->getApi('user');
        $res = $api->create([
            'login' => 'asdf',
            'lastname' => 'asdf',
            'firstname' => 'asdf',
            'mail' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/users.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, []);
        $res = json_decode($res, true);

        $this->assertEquals('/users/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all();
        $res = json_decode($res, true);

        $this->assertEquals('/users.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1, ['include' => ['memberships', 'groups']]);
        $res = json_decode($res, true);

        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1, ['include' => ['memberships', 'groups', 'parameter1']]);
        $res = json_decode($res, true);

        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('memberships,groups,parameter1'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1, ['include' => ['parameter1', 'memberships', 'groups']]);
        $res = json_decode($res, true);

        $this->assertEquals($res['path'], '/users/1.json?include='.urlencode('parameter1,memberships,groups'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/users/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testVersion()
    {
        $api = $this->client->getApi('version');
        $res = $api->create('testProject', [
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/versions.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->update(1, []);
        $res = json_decode($res, true);

        $this->assertEquals('/versions/1.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/versions.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);
        $res = json_decode($res, true);

        $this->assertEquals('/versions/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/versions/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testWiki()
    {
        $api = $this->client->getApi('wiki');
        $res = $api->create('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->update('testProject', 'about', [
            'text' => 'asdf',
            'comments' => 'asdf',
            'version' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('PUT', $res['method']);

        $res = $api->all('testProject');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/index.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show('testProject', 'about');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/about.json?include=attachments', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show('testProject', 'about', 'v1');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/about/v1.json?include=attachments', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove('testProject', 'about');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }
}

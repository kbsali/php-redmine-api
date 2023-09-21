<?php

namespace Redmine\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Redmine\Tests\Fixtures\MockClient;

class UrlTest extends TestCase
{
    public function testAttachment()
    {
        /** @var \Redmine\Api\Attachment */
        $api = MockClient::create()->getApi('attachment');
        $res = $api->show(1);

        $this->assertEquals('/attachments/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->upload('asdf');
        $res = json_decode($res, true);

        $this->assertEquals('/uploads.json', $res['path']);
        $this->assertEquals('POST', $res['method']);
    }

    public function testCustomFields()
    {
        /** @var \Redmine\Api\CustomField */
        $api = MockClient::create()->getApi('custom_fields');
        $res = $api->all();

        $this->assertEquals('/custom_fields.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testGroup()
    {
        /** @var \Redmine\Api\Group */
        $api = MockClient::create()->getApi('group');
        $res = $api->create([
            'name' => 'asdf',
        ]);
        $res = json_decode($res, true);

        $this->assertEquals('/groups.xml', $res['path']);
        $this->assertEquals('POST', $res['method']);

        $res = $api->all();

        $this->assertEquals('/groups.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals('/groups/1.json', $res['path']);
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
        /** @var \Redmine\Api\Issue */
        $api = MockClient::create()->getApi('issue');
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

        $this->assertEquals('/issues.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->all(['limit' => 250]);

        $this->assertEquals('/issues.json?limit=100&offset=0', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals('/issues/1.json', $res['path']);
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
        /** @var \Redmine\Api\IssueCategory */
        $api = MockClient::create()->getApi('issue_category');
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

        $this->assertEquals('/projects/testProject/issue_categories.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals('/issue_categories/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/issue_categories/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);

        $res = $api->remove(1, ['reassign_to_id' => 16]);
        $res = json_decode($res, true);

        $this->assertEquals('/issue_categories/1.xml?reassign_to_id=16', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testIssuePriority()
    {
        /** @var \Redmine\Api\IssuePriority */
        $api = MockClient::create()->getApi('issue_priority');
        $res = $api->all();

        $this->assertEquals('/enumerations/issue_priorities.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testIssueRelation()
    {
        /** @var \Redmine\Api\IssueRelation */
        $api = MockClient::create()->getApi('issue_relation');
        $res = $api->all(1);

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
        /** @var \Redmine\Api\IssueStatus */
        $api = MockClient::create()->getApi('issue_status');
        $res = $api->all();

        $this->assertEquals('/issue_statuses.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testMembership()
    {
        /** @var \Redmine\Api\Membership */
        $api = MockClient::create()->getApi('membership');
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

        $this->assertEquals('/projects/testProject/memberships.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/memberships/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testNews()
    {
        /** @var \Redmine\Api\News */
        $api = MockClient::create()->getApi('news');
        $res = $api->all();

        $this->assertEquals('/news.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->all('testProject');

        $this->assertEquals('/projects/testProject/news.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testProject()
    {
        /** @var \Redmine\Api\Project */
        $api = MockClient::create()->getApi('project');
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

        $this->assertEquals('/projects.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals($res['path'], '/projects/1.json?include='.urlencode('trackers,issue_categories,attachments,relations'));
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/projects/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testQuery()
    {
        /** @var \Redmine\Api\Query */
        $api = MockClient::create()->getApi('query');
        $res = $api->all();

        $this->assertEquals('/queries.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testRole()
    {
        /** @var \Redmine\Api\Role */
        $api = MockClient::create()->getApi('role');
        $res = $api->all();

        $this->assertEquals('/roles.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals('/roles/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testTimeEntry()
    {
        /** @var \Redmine\Api\TimeEntry */
        $api = MockClient::create()->getApi('time_entry');
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

        $this->assertEquals('/time_entries.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        // Test for #154: fix http_build_query encoding array values with numeric keys
        $res = $api->all([
            'f' => ['spent_on'],
            'op' => ['spent_on' => '><'],
            'v' => [
                'spent_on' => [
                    '2016-01-18',
                    '2016-01-22',
                ],
            ],
        ]);
        $this->assertEquals('/time_entries.json?limit=25&offset=0&f%5B%5D=spent_on&op%5Bspent_on%5D=%3E%3C&v%5Bspent_on%5D%5B%5D=2016-01-18&v%5Bspent_on%5D%5B%5D=2016-01-22', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals('/time_entries/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/time_entries/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testTimeEntryActivity()
    {
        /** @var \Redmine\Api\TimeEntryActivity */
        $api = MockClient::create()->getApi('time_entry_activity');
        $res = $api->all();

        $this->assertEquals('/enumerations/time_entry_activities.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testTracker()
    {
        /** @var \Redmine\Api\Tracker */
        $api = MockClient::create()->getApi('tracker');
        $res = $api->all();

        $this->assertEquals('/trackers.json', $res['path']);
        $this->assertEquals('GET', $res['method']);
    }

    public function testUser()
    {
        /** @var \Redmine\Api\User */
        $api = MockClient::create()->getApi('user');
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
        $res = json_decode($res, true);

        $this->assertEquals('/users/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testVersion()
    {
        /** @var \Redmine\Api\Version */
        $api = MockClient::create()->getApi('version');
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

        $this->assertEquals('/projects/testProject/versions.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show(1);

        $this->assertEquals('/versions/1.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove(1);
        $res = json_decode($res, true);

        $this->assertEquals('/versions/1.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }

    public function testWiki()
    {
        /** @var \Redmine\Api\Wiki */
        $api = MockClient::create()->getApi('wiki');
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

        $this->assertEquals('/projects/testProject/wiki/index.json', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show('testProject', 'about');

        $this->assertEquals('/projects/testProject/wiki/about.json?include=attachments', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->show('testProject', 'about', 'v1');

        $this->assertEquals('/projects/testProject/wiki/about/v1.json?include=attachments', $res['path']);
        $this->assertEquals('GET', $res['method']);

        $res = $api->remove('testProject', 'about');
        $res = json_decode($res, true);

        $this->assertEquals('/projects/testProject/wiki/about.xml', $res['path']);
        $this->assertEquals('DELETE', $res['method']);
    }
}

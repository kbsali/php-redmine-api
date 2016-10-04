<?php

/**
 * @file
 * This file holds example commands for reading, creating, updating and deleting redmine components.
 */

// As this is only an example file, we make sure, this is not accidently executed and may destroy real
// life content.
return;

require_once 'vendor/autoload.php';

// ----------------------------
// Random values used for the examples
$attachmentId = 12;
$categoryId = 2;
$groupId = 5;
$issueId = 5;
$issueRelationId = 5;
$membershipId = 123;
$projectId = 1;
$timeEntryId = 14;
$trackerId = 2;
$userId = 3;
$versionId = 2;

// ----------------------------
// Instanciate a redmine client
// --> with ApiKey
$client = new Redmine\Client('http://redmine.example.com', '1234567890abcdfgh');

// --> with Username/Password
$client = new Redmine\Client('http://redmine.example.com', 'username', 'password');

// ----------------------------
// [OPTIONAL] if you want to check
// the servers' SSL certificate on Curl call
$client->setCheckSslCertificate(true);

// ----------------------------
// [OPTIONAL] set the port
// (it will try to guess it from the url)
$client->setPort(8080);

// ----------------------------
// Trackers
$client->tracker->all();
$client->tracker->listing();

// ----------------------------
// Issue statuses
$client->issue_status->all();
$client->issue_status->listing();
$client->issue_status->getIdByName('New');

// ----------------------------
// Project
$client->project->all();
$client->project->all(array(
    'limit' => 10,
));
$client->project->listing();
$client->project->listing();
$client->project->show($projectId);
$client->project->getIdByName('Elvis');
$client->project->create(array(
    'name' => 'some name',
    'identifier' => 'the_identifier',
    'tracker_ids' => array(),
));
$client->project->update($projectId, array(
    'name' => 'different name',
));
$client->project->remove($projectId);

// ----------------------------
// Users
$client->user->all();
$client->user->listing();
$client->user->getCurrentUser(array(
    'include' => array(
        'memberships',
        'groups',
        'api_key',
        'status',
    )
));
$client->user->getIdByUsername('kbsali');
$client->user->show($userId, array(
    'include' => array(
        'memberships',
        'groups',
        'api_key',
        'status',
    )
));
$client->user->update($userId, array(
    'firstname' => 'Raul',
));
$client->user->remove($userId);
$client->user->create(array(
    'login' => 'test',
    'firstname' => 'test',
    'lastname' => 'test',
    'mail' => 'test@example.com',
));

// ----------------------------
// Issues
$client->issue->show($issueId);
$client->issue->all(array(
    'limit' => 100,
));
$client->issue->all(array('category_id' => $categoryId));
$client->issue->all(array('tracker_id' => $trackerId));
$client->issue->all(array('status_id' => 'closed'));
$client->issue->all(array('assigned_to_id' => $userId));
$client->issue->all(array('project_id' => 'test'));
$client->issue->all(array(
    'offset' => 100,
    'limit' => 100,
    'sort' => 'id',
    'project_id' => 'test',
    'tracker_id' => $trackerId,
    'status_id' => 'open',
    'assigned_to_id' => $userId,
    // 'cf_x'        => ,
    'query_id' => 3,
    'cf_1' => 'some value of this custom field', // where 1 = id of the customer field
    //  cf_SOME_CUSTOM_FIELD_ID => 'value'
));
$client->issue->create(array(
    'project_id' => 'test',
    'subject' => 'test api (xml) 3',
    'description' => 'test api',
    'assigned_to_id' => $userId,
    'custom_fields' => array(
        array(
            'id' => 2,
            'name' => 'Issuer',
            'value' => $_POST['ISSUER'],
        ),
        array(
            'id' => 5,
            'name' => 'Phone',
            'value' => $_POST['PHONE'],
        ),
        array(
            'id' => '8',
            'name' => 'Email',
            'value' => $_POST['EMAIL'],
        ),
    ),
    'watcher_user_ids' => array(),
));
$client->issue->update($issueId, array(
    // 'subject'        => 'test note (xml) 1',
    // 'notes'          => 'test note api',
    // 'assigned_to_id' => $userId,
    // 'status_id'      => 2,
    'status' => 'Resolved',
    'priority_id' => 5,
    'due_date' => date('Y-m-d'),
));
$client->issue->setIssueStatus($issueId, 'Resolved');
$client->issue->addNoteToIssue($issueId, 'some comment');
$client->issue->remove($issueId);

// To upload a file + attach it to an existing issue with $issueId
$upload = json_decode($client->attachment->upload($filecontent));
$client->issue->attach($issueId, array(
    'token' => $upload->upload->token,
    'filename' => 'MyFile.pdf',
    'description' => 'MyFile is better then YourFile...',
    'content_type' => 'application/pdf',
));

// Or, create a new issue with the file attached in one step
$upload = json_decode($client->attachment->upload($filecontent));
$client->issue->create(array(
    'project_id' => 'myproject',
    'subject' => 'A test issue',
    'description' => 'Here goes the issue description',
    'uploads' => array(
        array(
          'token' => $upload->upload->token,
          'filename' => 'MyFile.pdf',
          'description' => 'MyFile is better then YourFile...',
          'content_type' => 'application/pdf',
        ),
    ),
));

// ----------------------------
// Issue categories
$client->issue_category->all('project1');
$client->issue_category->listing($projectId);
$client->issue_category->show($categoryId);
$client->issue_category->getIdByName($projectId, 'Administration');
$client->issue_category->create('otherProject', array(
    'name' => 'test category',
));
$client->issue_category->update($categoryId, array(
    'name' => 'new category name',
));
$client->issue_category->remove($categoryId);
$client->issue_category->remove($categoryId, array(
    'reassign_to_id' => $userId,
));

// ----------------------------
// Versions
$client->version->all('test');
$client->version->listing('test');
$client->version->show($versionId);
$client->version->getIdByName('test', 'v2');
$client->version->create('test', array(
    'name' => 'v3432',
));
$client->version->update($versionId, array(
    'name' => 'v1121',
));
$client->version->remove($versionId);

// ----------------------------
// Attachments
$client->attachment->show($attachmentId);

$file_content = $client->attachment->download($attachmentId);
file_put_contents('example.png', $file_content);

// ----------------------------
// News
$client->news->all('test');
$client->news->all();

// ----------------------------
// Roles
$client->role->all();
$client->role->show(1);
$client->role->listing();

// ----------------------------
// Queries
$client->query->all();

// ----------------------------
// Time entries
$client->time_entry->all();
$client->time_entry->show($timeEntryId);
$client->time_entry->all(array(
    'issue_id' => 1234,
    'project_id' => 1234,
    'spent_on' => '2015-04-13',
    'user_id' => 168,
    'activity_id' => 13
));
$client->time_entry->create(array(
    'project_id' => $projectId,
    // 'issue_id' => 140,
    // 'spent_on' => null,
    'hours' => 12,
    'activity_id' => 8,
    'comments' => 'BOUH!',
    'custom_fields' => array(
        array(
            'id' => 1,
            'name' => 'Affected version',
            'value' => '1.0.1',
        ),
    ),
));
$client->time_entry->update($timeEntryId, array(
    'issue_id' => $issueId,
    // 'spent_on' => null,
    'hours' => 8,
    'activity_id' => 9,
    'comments' => 'blablabla!',
    'custom_fields' => array(
        array(
            'id' => 2,
            'name' => 'Resolution',
            'value' => 'Fixed',
        ),
    ),
));
$client->time_entry->remove($timeEntryId);

// ----------------------------
// Time entry activities
$client->time_entry_activity->all();

// ----------------------------
// Issue relations
$client->issue_relation->all($issueId);
$client->issue_relation->show($issueRelationId);
$client->issue_relation->remove($issueRelationId);

// ----------------------------
// Group (of members)
$client->group->all();
$client->group->listing();
$client->group->show($groupId, array('include' => 'users,memberships'));
$client->group->remove($groupId);
$client->group->addUser($groupId, $userId);
$client->group->removeUser($groupId, $userId);
$client->group->create(array(
    'name' => 'asdf',
    'user_ids' => array(1, 2)
));

// ----------------------------
// Project memberships
$client->membership->all($projectId);
$client->membership->create($projectId, array(
    'user_id' => null,
    'role_ids' => array(),
));
$client->membership->remove($membershipId);

// ----------------------------
// Issue priorities
$client->issue_priority->all();

// ----------------------------
// Wiki
$client->wiki->all('testProject');
$client->wiki->show('testProject', 'about');
$client->wiki->show('testProject', 'about', $version);
$client->wiki->create('testProject', 'about', array(
    'text' => null,
    'comments' => null,
    'version' => null,
));
$client->wiki->update('testProject', 'about', array(
    'text' => null,
    'comments' => null,
    'version' => null,
));
$client->wiki->remove('testProject', 'about');

// ----------------------------
// Issues' stats (see https://github.com/kbsali/php-redmine-api/issues/44)
$issues['all'] = $client->issue->all([
    'limit' => 1,
    'tracker_id' => 1,
    'status_id' => '*',
])['total_count'];

$issues['opened'] = $client->issue->all([
    'limit' => 1,
    'tracker_id' => 1,
    'status_id' => 'open',
])['total_count'];

$issues['closed'] = $client->issue->all([
    'limit' => 1,
    'tracker_id' => 1,
    'status_id' => 'closed',
])['total_count'];

print_r($issues);
/*
Array
(
    [all] => 8
    [opened] => 7
    [closed] => 1
)
*/

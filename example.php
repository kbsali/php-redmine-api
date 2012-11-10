<?php

require_once 'vendor/autoload.php';

$client = new Redmine\Client('http://redmine.example.com', '1234567890abcdfgh');

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
$client->api('tracker')->all();
$client->api('tracker')->listing();

// ----------------------------
// Issue statuses
$client->api('issue_status')->all();
$client->api('issue_status')->listing();
$client->api('issue_status')->getIdByName('New');

// ----------------------------
// Project
$client->api('project')->all();
$client->api('project')->all(array(
    'limit' => 10
));
$client->api('project')->listing();
$client->api('project')->listing();
$client->api('project')->show(1);
$client->api('project')->getIdByName('Elvis');
$client->api('project')->create(array(
    'name'       => 'some name',
    'identifier' => 'the_identifier',
));
$client->api('project')->update(4, array(
    'name' => 'different name',
));
$client->api('project')->remove(4);

// ----------------------------
// Users
$client->api('user')->all();
$client->api('user')->listing();
$client->api('user')->getIdByUsername('kbsali');
$client->api('user')->show(3);
$client->api('user')->update(3, array(
    'firstname' => 'Raul'
));
$client->api('user')->remove(7);
$client->api('user')->create(array(
    'login'     => 'test',
    'firstname' => 'test',
    'lastname'  => 'test',
    'mail'      => 'test@example.com',
));

// ----------------------------
// Issues
$client->api('issue')->show(32);
$client->api('issue')->all();
$client->api('issue')->all(array('category_id' => 2));
$client->api('issue')->all(array('tracker_id' => 3));
$client->api('issue')->all(array('tracker_id' => 'closed'));
$client->api('issue')->all(array('assigned_to_id' => 5));
$client->api('issue')->all(array('project_id' => 'test'));
$client->api('issue')->create(array(
    'project_id'     => 'test',
    'subject'        => 'test api (xml) 3',
    'description'    => 'test api',
    'assigned_to_id' => 3,
));
$client->api('issue')->update(140, array(
    // 'subject'        => 'test note (xml) 1',
    // 'notes'          => 'test note api',
    // 'assigned_to_id' => 5,
    // 'status_id'      => 2,
    'status'         => 'Resolved',
    'priority_id'    => 5,
    'due_date'       => date('Y-m-d'),
));

$client->api('issue')->setIssueStatus(140, 'Resolved');
$client->api('issue')->addNoteToIssue(140, 'some comment');

// ----------------------------
// Issue categories
$client->api('issue_category')->all('project1');
$client->api('issue_category')->listing(4);
$client->api('issue_category')->show(7);
$client->api('issue_category')->getIdByName(1, 'Administration');
$client->api('issue_category')->create('otherProject', array(
    'name' => 'test category',
));
$client->api('issue_category')->update(10, array(
    'name' => 'new category name',
));
$client->api('issue_category')->remove(10);

// ----------------------------
// Versions
$client->api('version')->all('test');
$client->api('version')->listing('test');
$client->api('version')->show(2);
$client->api('version')->getIdByName('test', 'v2');
$client->api('version')->create('test', array(
    'name' => 'v3432',
));
$client->api('version')->update(3, array(
    'name' => 'v1121',
));
$client->api('version')->remove(3);

// ----------------------------
// Attachments
$client->api('attachment')->show(1);

// ----------------------------
// News
$client->api('news')->all('test');
$client->api('news')->all();

// ----------------------------
// Roles
$client->api('role')->all();
$client->api('role')->listing();

// ----------------------------
// Queries
$client->api('query')->all();

// ----------------------------
// Time entries
$client->api('time_entry')->all();
$client->api('time_entry')->show(1);
$client->api('time_entry')->create(array(
    'project_id'    => 3,
    // 'issue_id'    => 140,
    // 'spent_on'    => null,
    'hours'       => 12,
    'activity_id' => 8,
    'comments'    => 'blbblblbla!',
));
$client->api('time_entry')->update(2, array(
    'issue_id'    => 140,
    // 'spent_on'    => null,
    'hours'       => 8,
    'activity_id' => 9,
    'comments'    => 'aaaaa!',
));
$client->api('time_entry')->remove(2);

// ----------------------------
// Issue relations
$client->api('issue_relation')->all(16);
$client->api('issue_relation')->show(2);
$client->api('issue_relation')->remove(2);

// ----------------------------
// Group (of members)
$client->api('group')->all();
$client->api('group')->listing();

// ----------------------------
// Project memberships
$client->api('membership')->all(1);
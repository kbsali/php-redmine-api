<?php

require_once 'vendor/autoload.php';

$client = new Redmine\Client('http://redmine.example.com', '1234567890abcdfgh');

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
$client->api('project')->listing();
$client->api('project')->listing();
$client->api('project')->show(1);
$client->api('project')->getIdByName('Elvis');
$client->api('project')->create(array(
    'name'       => 'some name',
    'identifier' => 'the_identifier',
));
$client->api('project')->update(4, array(
    'name'       => 'different name',
    // 'identifier' => 'the_identifier',
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
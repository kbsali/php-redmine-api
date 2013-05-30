<?php

require_once 'vendor/autoload.php';

// ----------------------------
// Random values used for the examples
$attachmentId    = 12;
$categoryId      = 2;
$groupId         = 5;
$issueId         = 5;
$issueRelationId = 5;
$membershipId    = 123;
$projectId       = 1;
$timeEntryId     = 14;
$trackerId       = 2;
$userId          = 3;
$versionId       = 2;

// ----------------------------
// Instanciate a redmine client
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
$client->api('project')->show($projectId);
$client->api('project')->getIdByName('Elvis');
$client->api('project')->create(array(
    'name'       => 'some name',
    'identifier' => 'the_identifier',
));
$client->api('project')->update($projectId, array(
    'name' => 'different name',
));
$client->api('project')->remove($projectId);

// ----------------------------
// Users
$client->api('user')->all();
$client->api('user')->listing();
$client->api('user')->getCurrentUser();
$client->api('user')->getIdByUsername('kbsali');
$client->api('user')->show($userId);
$client->api('user')->update($userId, array(
    'firstname' => 'Raul'
));
$client->api('user')->remove($userId);
$client->api('user')->create(array(
    'login'     => 'test',
    'firstname' => 'test',
    'lastname'  => 'test',
    'mail'      => 'test@example.com',
));

// ----------------------------
// Issues
$client->api('issue')->show($issueId);
$client->api('issue')->all();
$client->api('issue')->all(array('category_id'    => $categoryId));
$client->api('issue')->all(array('tracker_id'     => $trackerId));
$client->api('issue')->all(array('tracker_id'     => 'closed'));
$client->api('issue')->all(array('assigned_to_id' => $userId));
$client->api('issue')->all(array('project_id'     => 'test'));
$client->api('issue')->all(array(
    'offset'         => 100,
    'limit'          => 100,
    'sort'           => 'id',
    'project_id'     => 'test',
    'tracker_id'     => $trackerId,
    'status_id'      => 'open',
    'assigned_to_id' => $userId,
    // 'cf_x'        => ,
    'query_id'       => 3,
    'custom_fields'  => array(
        'id'    => SOME_CUSTOM_FIELD_ID,
        'value' => 'some value of this custom field',
    ),
));
$client->api('issue')->create(array(
    'project_id'     => 'test',
    'subject'        => 'test api (xml) 3',
    'description'    => 'test api',
    'assigned_to_id' => $userId,
    'custom_fields'  => array(
        array(
            'id'    => 2,
            'name'  => 'Issuer',
            'value' => $_POST['ISSUER'],
        ),
        array(
            'id'    => 5,
            'name'  => 'Phone',
            'value' => $_POST['PHONE'],
        ),
        array(
            'id'    => '8',
            'name'  => 'Email',
            'value' => $_POST['EMAIL'],
        ),
    ),
));
$client->api('issue')->update($issueId, array(
    // 'subject'        => 'test note (xml) 1',
    // 'notes'          => 'test note api',
    // 'assigned_to_id' => $userId,
    // 'status_id'      => 2,
    'status'            => 'Resolved',
    'priority_id'       => 5,
    'due_date'          => date('Y-m-d'),
));
$client->api('issue')->setIssueStatus($issueId, 'Resolved');
$client->api('issue')->addNoteToIssue($issueId, 'some comment');
$client->api('issue')->remove($issueId);

// To upload a file + attach it to an existing issue with $issueId
$upload = json_decode( $client->api('attachment')->upload($filecontent) );
$client->api('issue')->attach($issueId, array(
    'token'        => $upload->upload->token,
    'filename'     => 'MyFile.pdf',
    'description'  => 'MyFile is better then YourFile...',
    'content_type' => 'application/pdf'
));

// Or, create a new issue with the file attached in one step
$upload = json_decode( $client->api('attachment')->upload($filecontent) );
$client->api('issue')->create(array(
    'project_id'  => 'myproject',
    'subject'     => 'A test issue',
    'description' => 'Here goes the issue description',
    'uploads'     => array(
        array(
          'token'       => $upload->upload->token,
          'filename'    => 'MyFile.pdf',
          'description' => 'MyFile is better then YourFile...',
          'content_type'=> 'application/pdf'
        )
    )
));

// ----------------------------
// Issue categories
$client->api('issue_category')->all('project1');
$client->api('issue_category')->listing($projectId);
$client->api('issue_category')->show($categoryId);
$client->api('issue_category')->getIdByName($projectId, 'Administration');
$client->api('issue_category')->create('otherProject', array(
    'name' => 'test category',
));
$client->api('issue_category')->update($categoryId, array(
    'name' => 'new category name',
));
$client->api('issue_category')->remove($categoryId);
$client->api('issue_category')->remove($categoryId, array(
    'reassign_to_id' => $userId
));

// ----------------------------
// Versions
$client->api('version')->all('test');
$client->api('version')->listing('test');
$client->api('version')->show($versionId);
$client->api('version')->getIdByName('test', 'v2');
$client->api('version')->create('test', array(
    'name' => 'v3432',
));
$client->api('version')->update($versionId, array(
    'name' => 'v1121',
));
$client->api('version')->remove($versionId);

// ----------------------------
// Attachments
$client->api('attachment')->show($attachmentId);

// ----------------------------
// News
$client->api('news')->all('test');
$client->api('news')->all();

// ----------------------------
// Roles
$client->api('role')->all();
$client->api('role')->show(1);
$client->api('role')->listing();

// ----------------------------
// Queries
$client->api('query')->all();

// ----------------------------
// Time entries
$client->api('time_entry')->all();
$client->api('time_entry')->show($timeEntryId);
$client->api('time_entry')->create(array(
    'project_id'  => $projectId,
    // 'issue_id' => 140,
    // 'spent_on' => null,
    'hours'       => 12,
    'activity_id' => 8,
    'comments'    => 'BOUH!',
));
$client->api('time_entry')->update($timeEntryId, array(
    'issue_id'    => $issueId,
    // 'spent_on' => null,
    'hours'       => 8,
    'activity_id' => 9,
    'comments'    => 'blablabla!',
));
$client->api('time_entry')->remove($timeEntryId);

// ----------------------------
// Time entry activities
$client->api('time_entry_activity')->all();

// ----------------------------
// Issue relations
$client->api('issue_relation')->all($issueId);
$client->api('issue_relation')->show($issueRelationId);
$client->api('issue_relation')->remove($issueRelationId);

// ----------------------------
// Group (of members)
$client->api('group')->all();
$client->api('group')->listing();
$client->api('group')->show($groupId, array('include' => 'users,memberships'));
$client->api('group')->remove($groupId);
$client->api('group')->addUser($groupId, $userId);
$client->api('group')->removeUser($groupId, $userId);

// ----------------------------
// Project memberships
$client->api('membership')->all($projectId);
$client->api('membership')->create($projectId, array(
    'user_id'  => null,
    'role_ids' => null,
));
$client->api('membership')->remove($membershipId);

// ----------------------------
// Issue priorities
$client->api('issue_priority')->all();

// ----------------------------
// Wiki
$client->api('wiki')->all('testProject');
$client->api('wiki')->show('testProject', 'about');
$client->api('wiki')->show('testProject', 'about', $version);
$client->api('wiki')->create('testProject', 'about', array(
    'text'     => null,
    'comments' => null,
    'version'  => null,
));
$client->api('wiki')->remove('testProject', 'about');

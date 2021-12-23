# Usage

:warning: This file shows example commands for retrieving, creating, updating and
deleting Redmine data. As these are only examples, please **make sure, that you
do not execute them against your production instance as it may break real
content!**

## Installation

### With Composer

Please make sure you install `php-remine-api` with a PSR-4 autoloader like
[Composer](https://getcomposer.org/).

```bash
php composer.phar require kbsali/php-redmine-api
```

This will create a new entry in your `composer.json`.

```diff
{
    "require": {
+        "kbsali/php-redmine-api": "^1.6"
    }
}
```

### ... or without Composer

As an alternative you can download the code with all required dependencies from
[php-download.com](https://php-download.com/package/kbsali/redmine-api) and
unzip all files in your project folder. The service will create the autoloader
for you.

### Start your project

Create your project e.g. in the `index.php` by requiring the `vendor/autoload.php`
file.

```diff
+<?php
+
+require_once 'vendor/autoload.php';
```

### Instantiate a Redmine Client

You can choose between the native `cURL` client or the `PSR-18` compatible
client.

#### Native cURL Client `Redmine\Client\NativeCurlClient`

> :bulb: The `Redmine\Client\NativeCurlClient` class was introduced in `v1.8.0`.

Every client requires a URL to your Redmine instance and either a valid
Apikey...

```diff
<?php

require_once 'vendor/autoload.php';
+
+// Instantiate with ApiKey
+$client = new \Redmine\Client('http://localhost', '1234567890abcdfgh');
```

... or valid username/password.

```diff
<?php

require_once 'vendor/autoload.php';
+
+// Instantiate with Username/Password (not recommended)
+$client = new \Redmine\Client('http://redmine.example.com', 'username', 'password');
```

> :bulb: For security reason it is recommended that you use an ApiKey rather than your username/password.

After you instantiate a client you can set `cURL` options. Please note that for
functional reasons some `cURL` options (like `CURLOPT_CUSTOMREQUEST`,
    `CURLOPT_POST` and `CURLOPT_POSTFIELDS` ) can not be changed.

```diff
<?php

require_once 'vendor/autoload.php';

// Instantiate with ApiKey
$client = new \Redmine\Client('https://redmine.example.com', '1234567890abcdfgh');
+
+// [OPTIONAL] if you want to check the servers' SSL certificate on cURL call
+$client->setCurlOption(CURLOPT_SSL_VERIFYPEER, true);
+
+// [OPTIONAL] check the servers ssl version
+$client->setCurlOption(CURLOPT_SSLVERSION, CURL_SSLVERSION_DEFAULT);
+
+// [OPTIONAL] set custom http headers
+$client->setCurlOption(CURLOPT_HTTPHEADER, ['X-Redmine-API-Key: secret_access_key']);
```

#### Psr-18 compatible Client `Redmine\Client\Psr18Client`

> :bulb: The `Redmine\Client\Psr18Client` class was introduced in `v1.7.0`.

The `Psr18Client` requires

- a `Psr\Http\Client\ClientInterface` implementation (like guzzlehttp/guzzle) ([possible implementations](https://packagist.org/providers/psr/http-client-implementation))
- a `Psr\Http\Message\ServerRequestFactoryInterface` implementation (like nyholm/psr7) ([possible implementations](https://packagist.org/providers/psr/http-factory-implementation))
- a `Psr\Http\Message\StreamFactoryInterface` implementation (like nyholm/psr7) ([possible implementations](https://packagist.org/providers/psr/http-message-implementation))
- a URL to your Redmine instance
- an Apikey or username
- and optional a password if you want tu use username/password.

> :bulb: For security reason it is recommended that you use an ApiKey rather than your username/password.

```diff
<?php

require_once 'vendor/autoload.php';
+
+$guzzle = \GuzzleHttp\Client();
+$psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();
+
+// Instantiate with ApiKey
+$client = new Redmine\Client\Prs18Client($guzzle, $psr17Factory, $psr17Factory, 'https://redmine.example.com', '1234567890abcdfgh');
+// ...or Instantiate with Username/Password (not recommended)
+$client = new Redmine\Client\Prs18Client($guzzle, $psr17Factory, $psr17Factory, 'https://redmine.example.com', 'username', 'password');
```

##### Guzzle configuration

Because the `Psr18Client` is agnostic about the HTTP client implementation every
configuration specific to the transport has to be set to the
`Psr\Http\Client\ClientInterface` implementation.

This means that if you want to set any `cURL` settings to `Guzzle` you have
multiple ways to set them:

1. Using [Guzzle environment variables](https://docs.guzzlephp.org/en/stable/quickstart.html#environment-variables)
2. Using [request options](https://docs.guzzlephp.org/en/stable/request-options.html) inside a `Psr\Http\Client\ClientInterface` wrapper:

```diff
<?php

require_once 'vendor/autoload.php';

+use Psr\Http\Client\ClientInterface;
+use Psr\Http\Message\RequestInterface;
+use Psr\Http\Message\ResponseInterface;
+
$guzzle = \GuzzleHttp\Client();
$psr17Factory = new \GuzzleHttp\Psr7\HttpFactory();

+$guzzleWrapper = new class(\GuzzleHttp\Client $guzzle) implements ClientInterface
+{
+    private $guzzle;
+
+    public function __construct(\GuzzleHttp\Client $guzzle)
+    {
+        $this->guzzle = $guzzle;
+    }
+
+    public function sendRequest(RequestInterface $request): ResponseInterface
+    {
+        return $this->guzzle->send($request, [
+            // Set the options for every request here
+            'auth' => ['username', 'password', 'digest'],
+            'cert' => ['/path/server.pem', 'password'],
+            'connect_timeout' => 3.14,
+            // Set specific CURL options, see https://docs.guzzlephp.org/en/stable/faq.html#how-can-i-add-custom-curl-options
+            'curl' => [
+                CURLOPT_SSL_VERIFYPEER => 1,
+                CURLOPT_SSL_VERIFYHOST => 2,
+                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
+            ],
+        ]);
+    }
+};
+
// Instantiate with ApiKey
-$client = new \Redmine\Client\Prs18Client($guzzle, $psr17Factory, $psr17Factory, 'https://redmine.example.com', '1234567890abcdfgh');
+$client = new \Redmine\Client\Prs18Client($guzzleWrapper, $psr17Factory, $psr17Factory, 'https://redmine.example.com', '1234567890abcdfgh');
```

## Built-in Redmine features

### Impersonate User

Redmine allows you [to impersonate another user](https://www.redmine.org/projects/redmine/wiki/Rest_api#User-Impersonation).
This can be done using the methods `startImpersonateUser()` and `stopImpersonateUser()`.

```php
$client->startImpersonateUser('kim');
// all requests will now impersonate the user `kim`

// To stop impersonation
$client->stopImpersonateUser();
```

## Error handling

Every exception implement the interface `Redmine\Exception` making it easy to catch Redmine specific issues.

> :bulb: The `Redmine\Exception` interface was introduced in `v2.1.0`.

```php
try {
    $client->getApi('issue')->create($data);
} catch (\Redmine\Exception $e) {
    // exceptions from kbsali/redmine-api
} catch (\Throwable $e) {
    // other errors
}
```

## API

You can now use the `getApi()` method to create and get a specific Redmine API.

To check for failed requests you can afterwards check the status code via `$client->getLastResponseStatusCode()`.

```php
// ----------------------------
// Trackers
$client->getApi('tracker')->all();
$client->getApi('tracker')->listing();

// ----------------------------
// Issue statuses
$client->getApi('issue_status')->all();
$client->getApi('issue_status')->listing();
$client->getApi('issue_status')->getIdByName('New');

// ----------------------------
// Project
$client->getApi('project')->all();
$client->getApi('project')->all([
    'limit' => 10,
]);
$client->getApi('project')->listing();
$client->getApi('project')->listing();
$client->getApi('project')->show($projectId);
$client->getApi('project')->getIdByName('Elvis');
$client->getApi('project')->create([
    'name' => 'some name',
    'identifier' => 'the_identifier',
    'tracker_ids' => [],
]);
$client->getApi('project')->update($projectId, [
    'name' => 'different name',
]);
$client->getApi('project')->remove($projectId);

// ----------------------------
// Users
$client->getApi('user')->all();
$client->getApi('user')->listing();
$client->getApi('user')->getCurrentUser([
    'include' => [
        'memberships',
        'groups',
        'api_key',
        'status',
    ],
]);
$client->getApi('user')->getIdByUsername('kbsali');
$client->getApi('user')->show($userId, [
    'include' => [
        'memberships',
        'groups',
        'api_key',
        'status',
    ],
]);
$client->getApi('user')->update($userId, [
    'firstname' => 'Raul',
]);
$client->getApi('user')->remove($userId);
$client->getApi('user')->create([
    'login' => 'test',
    'firstname' => 'test',
    'lastname' => 'test',
    'mail' => 'test@example.com',
]);

// ----------------------------
// Issues
$client->getApi('issue')->show($issueId);
$client->getApi('issue')->all([
    'limit' => 100,
]);
$client->getApi('issue')->all(['category_id' => $categoryId]);
$client->getApi('issue')->all(['tracker_id' => $trackerId]);
$client->getApi('issue')->all(['status_id' => 'closed']);
$client->getApi('issue')->all(['assigned_to_id' => $userId]);
$client->getApi('issue')->all(['project_id' => 'test']);
$client->getApi('issue')->all([
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
]);
$client->getApi('issue')->create([
    'project_id' => 'test',
    'subject' => 'test api (xml) 3',
    'description' => 'test api',
    'assigned_to_id' => $userId,
    'custom_fields' => [
        [
            'id' => 2,
            'name' => 'Issuer',
            'value' => $_POST['ISSUER'],
        ],
        [
            'id' => 5,
            'name' => 'Phone',
            'value' => $_POST['PHONE'],
        ],
        [
            'id' => '8',
            'name' => 'Email',
            'value' => $_POST['EMAIL'],
        ],
    ],
    'watcher_user_ids' => [],
]);
$client->getApi('issue')->update($issueId, [
    // 'subject'        => 'test note (xml) 1',
    // 'notes'          => 'test note api',
    // 'assigned_to_id' => $userId,
    // 'status_id'      => 2,
    'status' => 'Resolved',
    'priority_id' => 5,
    'due_date' => date('Y-m-d'),
]);
$client->getApi('issue')->setIssueStatus($issueId, 'Resolved');
$client->getApi('issue')->addNoteToIssue($issueId, 'some comment');
$client->getApi('issue')->remove($issueId);

// To upload a file + attach it to an existing issue with $issueId
$upload = json_decode($client->getApi('attachment')->upload($filecontent));
$client->getApi('issue')->attach($issueId, [
    'token' => $upload->upload->token,
    'filename' => 'MyFile.pdf',
    'description' => 'MyFile is better then YourFile...',
    'content_type' => 'application/pdf',
]);

// Or, create a new issue with the file attached in one step
$upload = json_decode($client->getApi('attachment')->upload($filecontent));
$client->getApi('issue')->create([
    'project_id' => 'myproject',
    'subject' => 'A test issue',
    'description' => 'Here goes the issue description',
    'uploads' => [
        [
            'token' => $upload->upload->token,
            'filename' => 'MyFile.pdf',
            'description' => 'MyFile is better then YourFile...',
            'content_type' => 'application/pdf',
        ],
    ],
]);

// ----------------------------
// Issue categories
$client->getApi('issue_category')->all('project1');
$client->getApi('issue_category')->listing($projectId);
$client->getApi('issue_category')->show($categoryId);
$client->getApi('issue_category')->getIdByName($projectId, 'Administration');
$client->getApi('issue_category')->create('otherProject', [
    'name' => 'test category',
]);
$client->getApi('issue_category')->update($categoryId, [
    'name' => 'new category name',
]);
$client->getApi('issue_category')->remove($categoryId);
$client->getApi('issue_category')->remove($categoryId, [
    'reassign_to_id' => $userId,
]);

// ----------------------------
// Versions
$client->getApi('version')->all('test');
$client->getApi('version')->listing('test');
$client->getApi('version')->show($versionId);
$client->getApi('version')->getIdByName('test', 'v2');
$client->getApi('version')->create('test', [
    'name' => 'v3432',
]);
$client->getApi('version')->update($versionId, [
    'name' => 'v1121',
]);
$client->getApi('version')->remove($versionId);

// ----------------------------
// Attachments
$client->getApi('attachment')->show($attachmentId);

$file_content = $client->getApi('attachment')->download($attachmentId);
file_put_contents('example.png', $file_content);

// ----------------------------
// News
$client->getApi('news')->all('test');
$client->getApi('news')->all();

// ----------------------------
// Roles
$client->getApi('role')->all();
$client->getApi('role')->show(1);
$client->getApi('role')->listing();

// ----------------------------
// Queries
$client->getApi('query')->all();

// ----------------------------
// Time entries
$client->getApi('time_entry')->all();
$client->getApi('time_entry')->show($timeEntryId);
$client->getApi('time_entry')->all([
    'issue_id' => 1234,
    'project_id' => 1234,
    'spent_on' => '2015-04-13',
    'user_id' => 168,
    'activity_id' => 13,
]);
$client->getApi('time_entry')->create([
    'project_id' => $projectId,
    // 'issue_id' => 140,
    // 'spent_on' => null,
    'hours' => 12,
    'activity_id' => 8,
    'comments' => 'BOUH!',
    'custom_fields' => [
        [
            'id' => 1,
            'name' => 'Affected version',
            'value' => '1.0.1',
        ],
    ],
]);
$client->getApi('time_entry')->update($timeEntryId, [
    'issue_id' => $issueId,
    // 'spent_on' => null,
    'hours' => 8,
    'activity_id' => 9,
    'comments' => 'blablabla!',
    'custom_fields' => [
        [
            'id' => 2,
            'name' => 'Resolution',
            'value' => 'Fixed',
        ],
    ],
]);
$client->getApi('time_entry')->remove($timeEntryId);

// ----------------------------
// Time entry activities
$client->getApi('time_entry_activity')->all();

// ----------------------------
// Issue relations
$client->getApi('issue_relation')->all($issueId);
$client->getApi('issue_relation')->show($issueRelationId);
$client->getApi('issue_relation')->remove($issueRelationId);

// ----------------------------
// Group (of members)
$client->getApi('group')->all();
$client->getApi('group')->listing();
$client->getApi('group')->show($groupId, ['include' => 'users,memberships']);
$client->getApi('group')->remove($groupId);
$client->getApi('group')->addUser($groupId, $userId);
$client->getApi('group')->removeUser($groupId, $userId);
$client->getApi('group')->create([
    'name' => 'asdf',
    'user_ids' => [1, 2],
]);

// ----------------------------
// Project memberships
$client->getApi('membership')->all($projectId);
$client->getApi('membership')->create($projectId, [
    'user_id' => null,
    'role_ids' => [],
]);
$client->getApi('membership')->remove($membershipId);

// ----------------------------
// Issue priorities
$client->getApi('issue_priority')->all();

// ----------------------------
// Wiki
$client->getApi('wiki')->all('testProject');
$client->getApi('wiki')->show('testProject', 'about');
$client->getApi('wiki')->show('testProject', 'about', $version);
$client->getApi('wiki')->create('testProject', 'about', [
    'text' => null,
    'comments' => null,
    'version' => null,
]);
$client->getApi('wiki')->update('testProject', 'about', [
    'text' => null,
    'comments' => null,
    'version' => null,
]);
$client->getApi('wiki')->remove('testProject', 'about');

// ----------------------------
// Issues' stats (see https://github.com/kbsali/php-redmine-api/issues/44)
$issues['all'] = $client->getApi('issue')->all([
    'limit' => 1,
    'tracker_id' => 1,
    'status_id' => '*',
])['total_count'];

$issues['opened'] = $client->getApi('issue')->all([
    'limit' => 1,
    'tracker_id' => 1,
    'status_id' => 'open',
])['total_count'];

$issues['closed'] = $client->getApi('issue')->all([
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

// ----------------------------
// Search
$client->getApi('search')->search('Myproject', ['limit' => 100]);
```

# PHP Redmine API

A simple PHP Object Oriented wrapper for Redmine API.

Uses [Redmine API](http://www.redmine.org/projects/redmine/wiki/Rest_api/).

## Features

* Follows PSR-4 conventions and coding standard: autoload friendly
* API entry points implementation state :
* OK Attachments
* OK Groups
* OK Custom Fields
* OK Issues
* OK Issue Categories
* OK Issue Priorities
* *NOK Issue Relations - only partially implemented*
* OK Issue Statuses
* OK News
* OK Projects
* OK Project Memberships
* OK Queries
* OK Roles
* OK Time Entries
* OK Time Entry Activities
* OK Trackers
* OK Users
* OK Versions
* OK Wiki

## Todo

* Check header's response code (especially for POST/PUT/DELETE requests)
* See http://stackoverflow.com/questions/9183178/php-curl-retrieving-response-headers-and-body-in-a-single-request/9183272#9183272
* Maybe Guzzle for handling http connections
* https://github.com/guzzle/guzzle

## Limitations

Redmine is missing some APIs for a full remote management of the data :
* List of activities & roles : http://www.redmine.org/issues/11464
* ...

A possible solution to this would be to create an extra APIs implementing the missing entry points. See existing effort in doing so : https://github.com/rschobbert/redmine-miss-api

## Requirements

* PHP ^7.3 || ^8.0
* The PHP [cURL](http://php.net/manual/en/book.curl.php) extension
* The PHP [SimpleXML](http://php.net/manual/en/book.simplexml.php) extension
* The PHP [JSON](http://php.net/manual/en/book.json.php) extension
* [PHPUnit](https://phpunit.de/) >= 9.0 (optional) to run the test suite
* "Enable REST web service" for your Redmine project (/settings/edit?tab=authentication)
* then obtain your *API access key* in your profile page : /my/account
* or use your *username & password* (not recommended)

## Install

### Composer

[Composer](http://getcomposer.org/download/) users can simply run:

```bash
$ php composer.phar require kbsali/redmine-api
```

at the root of their projects. To utilize the library, include
Composer's `vendor/autoload.php` in the scripts that will use the
`Redmine` classes.

For example,

```php
<?php
// This file is generated by Composer
require_once 'vendor/autoload.php';

$client = new Redmine\Client('http://redmine.example.com', 'username', 'password');
```

### Manual

It is also possible to install the library oneself, either locally to
a project or globally; say, in `/usr/share/php`.

Download the library from [php-download.com](https://php-download.com/package/kbsali/redmine-api). The advantage of using this site is that no Composer installation is required. This service will resolve all composer dependencies for you and create a zip archive with `vendor/autoload.php` for you.

Than extract the library somewhere. For example, the following steps extract v1.6.0 of the library into the `vendor/php-redmine-api-1.6.0` directory:

```bash
$ unzip kbsali_redmine_api_1.6.0.0_require.zip
$ rm kbsali_redmine_api_1.6.0.0_require.zip
```

Now, in any scripts that will use the `Redmine` classes, include the `vendor/autoload.php` file from the php-redmine-api directory. For
example,

```php
<?php
// This file ships with php-redmine-api
require 'vendor/php-redmine-api-1.6.0/vendor/autoload.php';

$client = new Redmine\Client('http://redmine.example.com', 'username', 'password');
```

### Running the test suite

You can run test suite to make sure the library will work properly on your system. Simply run `vendor/bin/phpunit` in the project's directory :

```
$ vendor/bin/phpunit
PHPUnit 9.5.3 by Sebastian Bergmann and contributors.

Warning:       No code coverage driver available

...............................................................  63 / 445 ( 14%)
............................................................... 126 / 445 ( 28%)
............................................................... 189 / 445 ( 42%)
............................................................... 252 / 445 ( 56%)
............................................................... 315 / 445 ( 70%)
............................................................... 378 / 445 ( 84%)
............................................................... 441 / 445 ( 99%)
....                                                            445 / 445 (100%)

Time: 00:00.102, Memory: 12.00 MB

OK (445 tests, 993 assertions)
```

## Basic usage of `php-redmine-api` client

```php
<?php

// For Composer users (this file is generated by Composer)
require_once 'vendor/autoload.php';

// Or if you've installed the library manually, use this instead.
// require 'vendor/php-redmine-api-x.y.z/src/autoload.php';

$client = new Redmine\Client('http://redmine.example.com', 'API_ACCESS_KEY');
//-- OR --
$client = new Redmine\Client('http://redmine.example.com', 'username', 'password');

$client->getApi('user')->all();
$client->getApi('user')->listing();

$client->getApi('issue')->create([
    'project_id'  => 'test',
    'subject'     => 'some subject',
    'description' => 'a long description blablabla',
    'assigned_to_id' => 123, // or 'assigned_to' => 'user1'
]);
$client->getApi('issue')->all([
    'limit' => 1000
]);
```

[See further examples and read more about usage in the docs](docs/usage.md).

## User Impersonation

As of Redmine V2.2 you can impersonate user through the REST API :

```php

$client = new Redmine\Client('http://redmine.example.com', 'API_ACCESS_KEY');

// impersonate user
$client->startImpersonateUser('jsmith');

// create a time entry for jsmith
$client->getApi('time_entry')->create($data);

// remove impersonation for further calls
$client->stopImpersonateUser();
```


### Thanks!

* Thanks to [Thomas Spycher](https://github.com/tspycher/) for the 1st version of the class.
* Thanks to [Thibault Duplessis aka. ornicar](https://github.com/ornicar) for the php-github-api library, great source of inspiration!
* And all the [contributors](https://github.com/kbsali/php-redmine-api/graphs/contributors)
* specially [JanMalte](https://github.com/JanMalte) for his impressive contribution to the test coverage! :)

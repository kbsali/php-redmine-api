# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/kbsali/php-redmine-api/compare/v2.7.0...v2.x)

## [v2.7.0](https://github.com/kbsali/php-redmine-api/compare/v2.6.0...v2.7.0) - 2024-07-10

### Added

- New method `Redmine\Api\CustomField::listNames()` for listing the ids and names of all custom fields.
- New method `Redmine\Api\Group::listNames()` for listing the ids and names of all groups.
- New method `Redmine\Api\IssueCategory::listNamesByProject()` for listing the ids and names of all issue categories of a project.
- New method `Redmine\Api\IssueStatus::listNames()` for listing the ids and names of all issue statuses.
- New method `Redmine\Api\Project::listNames()` for listing the ids and names of all projects.
- New method `Redmine\Api\Role::listNames()` for listing the ids and names of all roles.
- New method `Redmine\Api\TimeEntryActivity::listNames()` for listing the ids and names of all time entry activities.
- New method `Redmine\Api\Tracker::listNames()` for listing the ids and names of all trackers.
- New method `Redmine\Api\User::listLogins()` for listing the ids and logins of all users.
- New method `Redmine\Api\Version::listNamesByProject()` for listing the ids and names of all versions of a project.
- Support for Redmine 4.2.x was added.

### Changed

- Behaviour-driven tests are run against Redmine 4.2.10, 5.0.9 and 5.1.3.

### Deprecated

- `Redmine\Api\CustomField::listing()` is deprecated, use `\Redmine\Api\CustomField::listNames()` instead.
- `Redmine\Api\CustomField::getIdByName()` is deprecated, use `\Redmine\Api\CustomField::listNames()` instead.
- `Redmine\Api\Group::listing()` is deprecated, use `\Redmine\Api\Group::listNames()` instead.
- `Redmine\Api\IssueCategory::listing()` is deprecated, use `\Redmine\Api\IssueCategory::listNamesByProject()` instead.
- `Redmine\Api\IssueCategory::getIdByName()` is deprecated, use `\Redmine\Api\IssueCategory::listNamesByProject()` instead.
- `Redmine\Api\IssueStatus::listing()` is deprecated, use `\Redmine\Api\IssueStatus::listNames()` instead.
- `Redmine\Api\IssueStatus::getIdByName()` is deprecated, use `\Redmine\Api\IssueStatus::listNames()` instead.
- `Redmine\Api\Project::listing()` is deprecated, use `\Redmine\Api\Project::listNames()` instead.
- `Redmine\Api\Project::getIdByName()` is deprecated, use `\Redmine\Api\Project::listNames()` instead.
- `Redmine\Api\Role::listing()` is deprecated, use `\Redmine\Api\Role::listNames()` instead.
- `Redmine\Api\TimeEntryActivity::listing()` is deprecated, use `\Redmine\Api\TimeEntryActivity::listNames()` instead.
- `Redmine\Api\TimeEntryActivity::getIdByName()` is deprecated, use `\Redmine\Api\TimeEntryActivity::listNames()` instead.
- `Redmine\Api\Tracker::listing()` is deprecated, use `\Redmine\Api\Tracker::listNames()` instead.
- `Redmine\Api\Tracker::getIdByName()` is deprecated, use `\Redmine\Api\Tracker::listNames()` instead.
- `Redmine\Api\User::listing()` is deprecated, use `\Redmine\Api\User::listLogins()` instead.
- `Redmine\Api\User::getIdByUsername()` is deprecated, use `\Redmine\Api\User::listLogins()` instead.
- `Redmine\Api\Version::listing()` is deprecated, use `\Redmine\Api\Version::listNamesByProject()` instead.
- `Redmine\Api\Version::getIdByName()` is deprecated, use `\Redmine\Api\Version::listNamesByProject()` instead.

### Removed

- Tests and development files were removed from releases.

## [v2.6.0](https://github.com/kbsali/php-redmine-api/compare/v2.5.0...v2.6.0) - 2024-03-25

### Added

- New method `Redmine\Api\Attachment::update()` for updating attachments.
- New interface `Redmine\Http\HttpClient` for new minimalistic HTTP clients.
- New interface `Redmine\Http\Request` for sending data with new minimalistic HTTP clients.
- New method `Redmine\Api\...::getLastResponse()` to get the last response made by the API class.
- Add support for custom arrays in `Redmine\Serializer\XmlSerializer`.

### Changed

- Calling `Redmine\Api\IssueRelation::create()` without the mandatory parameter `issue_to_id` throws a `Redmine\Exception\MissingParameterException` instead of returning a error array

### Fixed

- Parameter types for IDs were fixed in API for attachments, groups, issues, project, users and versions.
- Return types were fixed in API for attachments, groups, time entries, issues, project, users, versions and wiki.
- Wiki pages with special characters are now handled correctly.
- `Redmine\Api\Attachment::download()` returns false on error instead of the HTML error page.

### Deprecated

- `Redmine\Api\AbstractApi::get()` is deprecated, use `\Redmine\Http\HttpClient::request()` instead.
- `Redmine\Api\AbstractApi::post()` is deprecated, use `\Redmine\Http\HttpClient::request()` instead.
- `Redmine\Api\AbstractApi::put()` is deprecated, use `\Redmine\Http\HttpClient::request()` instead.
- `Redmine\Api\AbstractApi::delete()` is deprecated, use `\Redmine\Http\HttpClient::request()` instead.
- The constant `Redmine\Api\Issue::PRIO_LOW` is deprecated.
- The constant `Redmine\Api\Issue::PRIO_NORMAL` is deprecated.
- The constant `Redmine\Api\Issue::PRIO_HIGH` is deprecated.
- The constant `Redmine\Api\Issue::PRIO_URGENT` is deprecated.
- The constant `Redmine\Api\Issue::PRIO_IMMEDIATE` is deprecated.

## [v2.5.0](https://github.com/kbsali/php-redmine-api/compare/v2.4.0...v2.5.0) - 2024-02-05

### Added

- Added support for updating groups with method `Redmine\Api\Group::update()`.
- New method `Redmine\Api\Project::close()` to close a project.
- New method `Redmine\Api\Project::reopen()` to reopen a project.
- New method `Redmine\Api\Project::archive()` to archive a project.
- New method `Redmine\Api\Project::unarchive()` to unarchive a project.
- New interface `Redmine\Http\Response` as a data object for Redmine server responses.
- New method `UnexpectedResponseException::getResponse()` to get the last response responsible for the exception.

### Changed

- The last response is saved in `Redmine\Api\AbstractApi` to prevent race conditions with `Redmine\Client\Client` implementations.

## [v2.4.0](https://github.com/kbsali/php-redmine-api/compare/v2.3.0...v2.4.0) - 2024-01-04

### Added

- Added support for PHP 8.3
- New method `Redmine\Api\CustomField::list()` to list custom fields.
- New method `Redmine\Api\Group::list()` to list groups.
- New method `Redmine\Api\Issue::list()` to list issues.
- New method `Redmine\Api\IssueCategory::listByProject()` to list issue categories from a project.
- New method `Redmine\Api\IssuePriority::list()` to list issue priorities.
- New method `Redmine\Api\IssueRelation::listByIssueId()` to list relations from an issue.
- New method `Redmine\Api\IssueStatus::list()` to list issue statuses.
- New method `Redmine\Api\Membership::listByProject()` to list memberships from a project.
- New method `Redmine\Api\News::list()` to list news from all project.
- New method `Redmine\Api\News::listByProject()` to list news from a project.
- New method `Redmine\Api\Project::list()` to list projects.
- New method `Redmine\Api\Query::list()` to list queries.
- New method `Redmine\Api\Role::list()` to list roles.
- New method `Redmine\Api\Search::listByQuery()` to list search results by query.
- New method `Redmine\Api\TimeEntry::list()` to list time entries.
- New method `Redmine\Api\TimeEntryActivity::list()` to list time entry activities.
- New method `Redmine\Api\Tracker::list()` to list trackers.
- New method `Redmine\Api\User::list()` to list users.
- New method `Redmine\Api\Version::listByProject()` to list versions from a project.
- New method `Redmine\Api\Wiki::listByProject()` to list wiki pages from a project.
- New exception `Redmine\Exception\UnexpectedResponseException` if the Redmine server responded with an unexpected body.

### Fixed

- Restore BC in possible return types in `Redmine\Api\...::all()` methods

### Deprecated

- `Redmine\Api\CustomField::all()` is deprecated, use `Redmine\Api\CustomField::list()` instead
- `Redmine\Api\Group::all()` is deprecated, use `Redmine\Api\Group::list()` instead
- `Redmine\Api\Issue::all()` is deprecated, use `Redmine\Api\Issue::list()` instead
- `Redmine\Api\IssueCategory::all()` is deprecated, use `Redmine\Api\IssueCategory::listByProject()` instead
- `Redmine\Api\IssuePriority::all()` is deprecated, use `Redmine\Api\IssuePriority::list()` instead
- `Redmine\Api\IssueRelation::all()` is deprecated, use `Redmine\Api\IssueRelation::listByIssueId()` instead
- `Redmine\Api\IssueStatus::all()` is deprecated, use `Redmine\Api\IssueStatus::list()` instead
- `Redmine\Api\Membership::all()` is deprecated, use `Redmine\Api\Membership::listByProject()` instead
- `Redmine\Api\News::all()` is deprecated, use `Redmine\Api\News::list()` or `Redmine\Api\News::listByProject()` instead
- `Redmine\Api\Project::all()` is deprecated, use `Redmine\Api\Project::list()` instead
- `Redmine\Api\Query::all()` is deprecated, use `Redmine\Api\Query::list()` instead
- `Redmine\Api\Role::all()` is deprecated, use `Redmine\Api\Role::list()` instead
- `Redmine\Api\Search::search()` is deprecated, use `Redmine\Api\Search::listByQuery()` instead
- `Redmine\Api\TimeEntry::all()` is deprecated, use `Redmine\Api\TimeEntry::list()` instead
- `Redmine\Api\TimeEntryActivity::all()` is deprecated, use `Redmine\Api\TimeEntryActivity::list()` instead
- `Redmine\Api\Tracker::all()` is deprecated, use `Redmine\Api\Tracker::list()` instead
- `Redmine\Api\User::all()` is deprecated, use `Redmine\Api\User::list()` instead
- `Redmine\Api\Version::all()` is deprecated, use `Redmine\Api\Version::listByProject()` instead
- `Redmine\Api\Wiki::all()` is deprecated, use `Redmine\Api\Wiki::listByProject()` instead

## [v2.3.0](https://github.com/kbsali/php-redmine-api/compare/v2.2.0...v2.3.0) - 2023-10-09

### Added

- New class `Redmine\Serializer\PathSerializer` to build an URL path with query parameters.
- New class `Redmine\Serializer\JsonSerializer` to encode or normalize JSON data.
- New class `Redmine\Serializer\XmlSerializer` to encode or normalize XML data.
- Allow `Psr\Http\Message\RequestFactoryInterface` as Argument #2 ($requestFactory) in `Redmine\Client\Psr18Client::__construct()`
- Added support for PHP 8.2

### Deprecated

- Providing Argument #2 ($requestFactory) in `Redmine\Client\Psr18Client::__construct()` as type `Psr\Http\Message\ServerRequestFactoryInterface` is deprecated, provide as type `Psr\Http\Message\RequestFactoryInterface` instead
- `Redmine\Api\AbstractApi::attachCustomFieldXML()` is deprecated, use `Redmine\Serializer\XmlSerializer::createFromArray()` instead
- `Redmine\Api\Project::prepareParamsXml()` is deprecated, use `Redmine\Serializer\XmlSerializer::createFromArray()` instead

## [v2.2.0](https://github.com/kbsali/php-redmine-api/compare/v2.1.1...v2.2.0) - 2022-03-01

### Added

- New method `Redmine\Client\AbstractApi::retrieveData()` to retrieve as many elements as you want as array (even if the total number of elements is greater than 100).
- New exception `Redmine\Client\SerializerException` for JSON/XML serializer related exceptions

### Fixed

- Allow unassign user from an issue

### Deprecated

- `Redmine\Api\AbstractApi::retrieveAll()` is deprecated, use `Redmine\Api\AbstractApi::retrieveData()` instead

## [v2.1.1](https://github.com/kbsali/php-redmine-api/compare/v2.1.0...v2.1.1) - 2022-01-15

### Fixed

- Special characters in comments when updating time entries will be escaped

## [v2.1.0](https://github.com/kbsali/php-redmine-api/compare/v2.0.1...v2.1.0) - 2022-01-04

### Added

- Added support for PHP 8.1
- New interface `Redmine\Exception` that is implemented by every library-related exception
- New exception `Redmine\Exception\ClientException` for client related exceptions
- New exception `Redmine\Exception\InvalidApiNameException` if an invalid API instance is requested
- New exception `Redmine\Exception\InvalidParameterException` for invalid parameter provided to an API instance
- New exception `Redmine\Exception\MissingParameterException` for missing parameter while using an API instance

### Changed

- Switched from Travis-CI to Github Actions

### Fixed

- Avoid warning if path of uploaded file is longer than the maximum allowed path length

### Deprecated

- `Redmine\Api\AbstractApi::lastCallFailed()` is deprecated, use `Redmine\Client\Client::getLastResponseStatusCode()` instead
- Uploading an attachment using `Redmine\Api\Attachment::upload()` with filepath is deprectead, use `file_get_contents()` to upload the file content instead

## [v2.0.1](https://github.com/kbsali/php-redmine-api/compare/v2.0.0...v2.0.1) - 2021-09-22

### Fixed

- Fixed the handling of a response if the content type header is missing

## [v2.0.0](https://github.com/kbsali/php-redmine-api/compare/v1.8.1...v2.0.0) - 2021-06-08

### Removed

- **BREAKING**: Deprecated client `Redmine\Client` was removed, use `Redmine\Client\NativeCurlClient` or `Redmine\Client\Psr18Client` instead
- **BREAKING**: `src/autoload.php` was removed, use the `vendor/autoload.php` from Composer instead

## [v1.8.1](https://github.com/kbsali/php-redmine-api/compare/v1.8.0...v1.8.1) - 2021-06-01

### Fixed

- `AbstractApi::get()` returns `null` on empty response body instead of `false` for BC reasons
- Use uppercase in HTTP verbs

## [v1.8.0](https://github.com/kbsali/php-redmine-api/compare/v1.7.0...v1.8.0) - 2021-04-19

### Added

- New native cURL client `Redmine\Client\NativeCurlClient` as a replacement for  `Redmine\Client`
- This `CHANGELOG.md` file

### Changed

- Better type checking thanks to typed properties
- Move `example.php` into new `docs` folder

### Removed

- Drop support for PHP 7.3

### Fixed

- `Redmine\Client::getCheckSslHost()` always returns as boolean

### Deprecated

- `Redmine\Client` is deprecated, use `Redmine\Client\NativeCurlClient` or `Redmine\Client\Psr18Client` instead
- Magic getter in `Redmine\Client` is deprecated, use `Redmine\Client::getApi()` instead
- `Redmine\Client::api()` is deprecated, use `Redmine\Client::getApi()` instead
- `Redmine\Client::get()` is deprecated, use `Redmine\Client::requestGet()` instead
- `Redmine\Client::post()` is deprecated, use `Redmine\Client::requestPost()` instead
- `Redmine\Client::put()` is deprecated, use `Redmine\Client::requestPut()` instead
- `Redmine\Client::delete()` is deprecated, use `Redmine\Client::requestDelete()` instead
- `Redmine\Client::setCheckSslCertificate()` is deprecated, use `Redmine\Client::setCurlOption()` instead
- `Redmine\Client::setCheckSslHost()` is deprecated, use `Redmine\Client::setCurlOption()` instead
- `Redmine\Client::setSslVersion()` is deprecated, use `Redmine\Client::setCurlOption()` instead
- `Redmine\Client::setUseHttpAuth()` is deprecated, use `Redmine\Client::setCurlOption()` instead
- `Redmine\Client::setPort()` is deprecated, use `Redmine\Client::setCurlOption()` instead
- `Redmine\Client::getResponseCode()` is deprecated, use `Redmine\Client::getLastResponseStatusCode()` instead
- `Redmine\Client::setImpersonateUser()` is deprecated, use `Redmine\Client::startImpersonateUser()` and `Redmine\Client::stopImpersonateUser()` instead
- `Redmine\Client::setCustomHost()` is deprecated, use `Redmine\Client::setCurlOption()` instead
- `Redmine\Client::getUrl()` is deprecated, you should stop using it
- `Redmine\Client::decode()` is deprecated, you should stop using it
- `Redmine\Client::getCheckSslCertificate()` is deprecated, you should stop using it
- `Redmine\Client::getCheckSslHost()` is deprecated, you should stop using it
- `Redmine\Client::getSslVersion()` is deprecated, you should stop using it
- `Redmine\Client::getUseHttpAuth()` is deprecated, you should stop using it
- `Redmine\Client::getPort()` is deprecated, you should stop using it
- `Redmine\Client::getImpersonateUser()` is deprecated, you should stop using it
- `Redmine\Client::getCustomHost()` is deprecated, you should stop using it
- `Redmine\Client::getCurlOptions()` is deprecated, you should stop using it
- `Redmine\Client::prepareRequest()` is deprecated, you should stop using it
- `Redmine\Client::processCurlResponse()` is deprecated, you should stop using it
- `Redmine\Client::runRequest()` is deprecated, you should stop using it

## [v1.7.0](https://github.com/kbsali/php-redmine-api/compare/v1.6.0...v1.7.0) - 2021-03-22

### Added

- New interface `Redmine\Client\Client` for all clients
- New PSR-18 based client `Redmine\Client\Psr18Client` for usage with e.g. `Guzzle`
- New method `Redmine\Client::getApi()` for returning an API instance, `Redmine\Client::api()` and magic getter `Redmine\Client->issue` will be deprecated in future.
- New method `Redmine\Client::startImpersonateUser()` to set an impersonated user, `Redmine\Client::setImpersonateUser()` will be deprecated in future.
- New method `Redmine\Client::stopImpersonateUser()` to stop impersonating an user.
- New method `Redmine\Client::requestGet()` to create and send a GET request, `Redmine\Client::get()` will be deprecated in future.
- New method `Redmine\Client::requestPost()` to create and send a POST request, `Redmine\Client::post()` will be deprecated in future.
- New method `Redmine\Client::requestPut()` to create and send a PUT request, `Redmine\Client::put()` will be deprecated in future.
- New method `Redmine\Client::requestDelete()` to create and send a DELETE request, `Redmine\Client::delete()` will be deprecated in future.
- New method `Redmine\Client::getLastResponseStatusCode()` returns status code of the last response, `Redmine\Client::getResponseCode()` will be deprecated in future.
- New method `Redmine\Client::getLastResponseContentType()` returns the content type of the last response.
- New method `Redmine\Client::getLastResponseBody()` returns the raw body of the last response.

### Changed

- Move JSON and XML decoding directly into `Redmine\Api\AbstractApi` instead of the client.

### Fixed

- escape special chars in title, description, etc in wiki, issue, project and time_entry api.

## [v1.6.0](https://github.com/kbsali/php-redmine-api/compare/v1.5.22...v1.6.0) - 2021-01-02

### Added

- Added support for PHP 8.0
- New method `Redmine\Api\Attachment::remove()` to delete an attachment
- New method `Redmine\Api\TimeEntryActivity::listing()` to list time entry activities
- New method `Redmine\Api\TimeEntryActivity::getIdByName()` to get a time entry activity id by its name

### Removed

- Removed support for PHP 5.6, 7.0, 7.1 and 7.2

## [v1.5.22](https://github.com/kbsali/php-redmine-api/compare/v1.5.21...v1.5.22) - 2020-08-07

### Added

- Added support for filename parameter to attachment upload
- Added file upload with wiki

### Fixed

- Fixed a warning on file upload
- Fixed a lot of warnings related to `custom_field`
- Fixed custom field file type

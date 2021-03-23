# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/kbsali/php-redmine-api/compare/v1.7.0...master)

### Added

- This `CHANGELOG.md` file

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

## [v1.5.22](https://github.com/kbsali/php-redmine-api/compare/v1.5.21...v1.5.22) - 2021-01-02

### Added

- Added support for filename parameter to attachment upload
- Added file upload with wiki

### Fixed

- Fixed a warning on file upload
- Fixed a lot of warnings related to `custom_field`
- Fixed custom field file type

# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased](https://github.com/kbsali/php-redmine-api/compare/v2.2.0...v2.x)

### Added

- Allow `Psr\Http\Message\RequestFactoryInterface` as Argument #2 ($requestFactory) in `Redmine\Client\Psr18Client::__construct()`
- Added support for PHP 8.2

### Deprecated

- Providing Argument #2 ($requestFactory) in `Redmine\Client\Psr18Client::__construct()` as type `Psr\Http\Message\ServerRequestFactoryInterface` is deprecated, provide as type `Psr\Http\Message\RequestFactoryInterface` instead
- `Redmine\Api\AbstractApi::attachCustomFieldXML()` is deprecated
- `Redmine\Api\Project::prepareParamsXml()` is deprecated

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

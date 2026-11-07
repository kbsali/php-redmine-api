# Backward compatibility

This document describes what users of `kbsali/php-redmine-api` can rely on when they update the library.

## Who is affected

The library is a Composer package (`kbsali/php-redmine-api`). Its users call the API classes, use the clients
and, in some cases, extend or implement library classes.

## Versioning

The library follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html). Changes are listed in
[CHANGELOG.md](../CHANGELOG.md) under `Added`, `Changed`, `Deprecated`, `Removed` and `Fixed`.

- Patch releases (`2.x.y`) contain bug fixes only.
- Minor releases (`2.x.0`) add features and deprecations and do not break the public API.
- A major release (`3.0.0`) may remove deprecated parts and break the API.

## What is covered

The public classes, interfaces, methods and constants in the `Redmine\` namespace (`src/Redmine/`), including
the exceptions in `Redmine\Exception\`.

## What is not covered

- Everything marked `@internal` (for example the traits `ClientApiTrait` and `ResponseValidationTrait`).
- Private members.
- The test code (`Redmine\Tests\`).
- The exact wording of exception and deprecation messages. A message may change in a minor release, and the
  change is listed in the changelog.

## Extending and implementing

Classes of the `Redmine\Api\` namespace are meant to be created with `fromHttpClient()` and to be wrapped, not
extended. Their constructors and the possibility to extend them are deprecated and will end in `3.0.0`.
Custom clients implement the interfaces `Redmine\Client\Client` and `Redmine\Http\HttpClient`.

## Deprecations

A deprecated part is marked with `@deprecated` in the code and triggers an `E_USER_DEPRECATED` message that names
the version it was deprecated in and the replacement. It is listed under `Deprecated` in the changelog.
A deprecated part stays at least until the next major release.

## Future mode

Where a change cannot be announced with a deprecation message, for example when a return value would turn into an
exception, the new behavior is available through `$client->enableFutureMode()` first and becomes the default in
a major release.

## Supported versions

The supported PHP versions are in `composer.json`, the tested Redmine versions in the README.

## Exceptions to the promise

Security fixes and bug fixes may change behavior that users relied on, when keeping it would be unsafe or wrong.
They are listed in the changelog.

## How the promise is checked

The tests, PHPStan and a Rector BC library (`art4/rector-bc-library`) run on every change. There is no automatic
comparison of the public API with the last release.

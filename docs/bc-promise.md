# Backward compatibility

This document describes what users of `kbsali/redmine-api` can rely on when they update the library.

## Who is affected

The library is a Composer package (`kbsali/redmine-api`). Its users call the API classes, use the clients
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

- Everything marked `@internal`.
- Private members.
- The test code (`Redmine\Tests\`).
- The exact wording of exception and deprecation messages. A message may change in a minor release, and the
  change is listed in the changelog.

## Deprecations

A deprecated part is listed under `Deprecated` in the changelog. It stays at least until the next major release.

## Requirements

The PHP version, the PHP extensions and the dependencies the package needs are in the `require` section of
`composer.json`, and Composer installs the package accordingly. Changing them is not a breaking change as long as
the public API stays the same. The tested Redmine versions are in the README.

## Exceptions to the promise

Security fixes and bug fixes may change behavior that users relied on, when keeping it would be unsafe or wrong.
They are listed in the changelog.

## How the promise is checked

The CI runs on every pull request and on every push to `v2.x` and `v3.x`. It runs the tests, PHPStan, the code
style check and Rector. The Rector configuration includes a BC library (`art4/rector-bc-library`) for type
declarations. In addition, the job `BC check` compares the public API with the last release tag using the
[Roave Backward Compatibility Check](https://github.com/Roave/BackwardCompatibilityCheck).

`composer test` runs PHPStan, the tests, Rector and the code style check locally. It does not include the Roave
check.

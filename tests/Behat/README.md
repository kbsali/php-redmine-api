# Behaviour tests

This folder contains BDD tests using Docker and Behat.

## How to run the tests

Pull the Redmine docker images and start them by running:

```bash
docker compose up -d
```

Now you can run all behaviour-driven tests grouped by Redmine version using the `bdt` command:

```bash
# all tests
docker compose exec behat composer run bdt
```

If you need more control about the behat tests or want to change the output format,
you can use the `behat` command directly:

```bash
# test only a specific redmine version
docker compose exec behat composer run behat -- --suite=redmine_6_1
# test only specific endpoints
docker compose exec behat composer run behat -- --tags=issue,group
# test only specific endpoints on a specific redmine version
docker compose exec behat composer run behat -- --suite=redmine_6_1 --tags=issue,group
# test only a specific redmine version and format the output as `progress` (default is `pretty`)
docker compose exec behat composer run behat -- --suite=redmine_6_1 --format=progress
```

## Future mode

The client from the step `Given I have a "NativeCurlClient" client` runs with the future mode enabled
(see `Redmine\Client\NativeCurlClient::enableFutureMode()`).
In future mode the API methods throw an `UnexpectedResponseException` on an unexpected response.

A `When` step that calls the API with `FeatureContext::registerClientCall()` catches this exception
and registers the response of the exception as the last response, so you can assert the exception and the response:

```
    @error
    Scenario: Showing a not existing issue
        Given I have a "NativeCurlClient" client
        When I show the issue with id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
```

A scenario fails, if an exception was thrown but not asserted.
The steps `the returned data ...` fail after an exception, because there is no returned data.

### Without future mode

If the behaviour without future mode differs, add a second scenario directly below.
Use the step `Given I have a "NativeCurlClient" client without future mode`,
add the suffix "without future mode" to the title and tag the scenario with `@without-future-mode`:

```
    @error @without-future-mode
    Scenario: Showing a not existing issue without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I show the issue with id "10"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false
```

In v3.0.0 the future mode becomes the only behaviour.
Then the step and all scenarios tagged with `@without-future-mode` will be removed.

## Redmine version specific features

Some Redmine features are specific for a Redmine version. These situations are handled in different ways.

### Modified Rest-API Responses

It is possible that a new Redmine version returns new or changed elements in a response.
This can be handled on the `step` layer (note the missing `homepage` property in `< 5.1.0`):

```
        And the returned data "projects.0" property has only the following properties with Redmine version ">= 5.1.0"
            """
            id
            name
            identifier
            description
            homepage
            """
        But the returned data "projects.0" property has only the following properties with Redmine version ">= 5.1.0 < 6.0.0"
            """
            id
            name
            identifier
            description
            homepage
            """
        But the returned data "projects.0" property has only the following properties with Redmine version "< 5.1.0"
            """
            id
            name
            identifier
            description
            """
```

### New Rest-API Endpoints

A new Redmine version could introduce new REST-API endpoints.
Tests for this endpoint should not be run on older Redmine versions.
This can be handled on the `scenario` or `feature` layer.

1. Tag features e.g. with `@since70000`.

```
@since70000
Feature: Interacting with a REST API endpoint added in 7.0.0
    [...]
```

2. Exclude the tag from the specific suite in the `behat.yml` (note the `~` prefix):

```
default:
    suites:
        [...]
        redmine_6_1:
            [...]
            filters:
                tags: "~@since70000"

```

### Removed Rest-API Endpoints

A new Redmine version could remove REST-API endpoints.
Tests for this endpoint should not be run on newer Redmine versions.
This can be handled on the `scenario` or `feature` layer.

1. Tag features e.g. with `@until70000`.

```
@until70000
Feature: Interacting with a REST API endpoint removed in 7.0.0
    [...]
```

or tag scenarios e.g. with `@until70000`.

```
    @until70000
    Scenario: Using a feature removed in 7.0.0
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        [...]
```

2. Exclude the tag from the specific suite in the `behat.yml` (note the `~` prefix):

```
default:
    suites:
        [...]
        redmine_7_0:
            [...]
            filters:
                tags: "~@until70000"

```

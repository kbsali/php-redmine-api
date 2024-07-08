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
docker compose exec php composer bdt
```

If you need more control about the behat tests or want to change the output format,
you can use the `behat` command directly:

```bash
# test only a specific redmine version
docker compose exec php composer behat -- --suite=redmine_50103
# test only specific endpoints
docker compose exec php composer behat -- --tags=issue,group
# test only specific endpoints on a specific redmine version
docker compose exec php composer behat -- --suite=redmine_50103 --tags=issue,group
# test only a specific redmine version and format the output as `progress` (default is `pretty`)
docker compose exec php composer behat -- --suite=redmine_50103 --format=progress
```

## Redmine version specific features

Some Redmine features are specific for a Redmine version. Theses situations are handled in different ways.

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

1. Tag features or scenarios e.g. with `@since50000`.

```
@since50000
Feature: Interacting with the new REST API endpoint
    [...]
```

or

```
    @since50000
    Scenario: Using a new feature
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        [...]
```

2. Exclude the tag from the specific suite in the `behat.yml` (note the `~` prefix):

```
default:
    suites:
        [...]
        redmine_40210:
            [...]
            filters:
                tags: "~@since50000"

```

### Removed Rest-API Endpoints

A new Redmine version could remove REST-API endpoints.
Tests for this endpoint should not be run on newer Redmine versions.
This can be handled on the `scenario` or `feature` layer.

1. Tag features or scenarios e.g. with `@until60000`.

```
@until60000
Feature: Interacting with the a REST API endpoint removed in 6.0.0
    [...]
```

or

```
    @until60000
    Scenario: Using a deprecated feature
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        [...]
```

2. Exclude the tag from the specific suite in the `behat.yml` (note the `~` prefix):

```
default:
    suites:
        [...]
        redmine_60000:
            [...]
            filters:
                tags: "~@until60000"

```

# Behaviour tests

This folder contains BDD tests using Docker and Behat.

## How to run the tests

Pull the Redmine docker images and start them by running:

```bash
docker compose up -d
```

Now you can run the tests:

```bash
# all tests
docker compose exec php composer behat
# only a specific redmine version
docker compose exec php composer behat -- --suite=redmine_50103
# only specific endpoints
docker compose exec php composer behat -- --tags=issue,group
# only specific endpoints on a specific redmine version
docker compose exec php composer behat -- --suite=redmine_50103 --tags=issue,group
```

## Redmine specific features

Some Redmine features are specific for a Redmine version. There are two ways to handle this situations.

### Modified Rest-API Responses

It is possible that a new Redmine version returns new or changed elements in a response.
This can be handled on the `step` layer:

```
        And the returned data "projects.0" property has only the following properties with Redmine version ">= 5.1.0"
            """
            id
            name
            identifier
            description
            homepage
            status
            is_public
            inherit_members
            created_on
            updated_on
            """
        But the returned data "projects.0" property has only the following properties with Redmine version "< 5.1.0"
            """
            id
            name
            identifier
            description
            status
            is_public
            inherit_members
            created_on
            updated_on
            """
```

### New Rest-API Endpoints

A new Redmine version could be introduce new REST-API endpoints that are missing in the older version.
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

A new Redmine version could remove REST-API endpoints that are missing in the newer versions.
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

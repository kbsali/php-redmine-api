# Behaviour tests

This folder contains BDD tests using Docker and Behat.

## How to run the tests

Pull the Redmine docker images and start them by running:

```bash
docker compose up -d
```

Now you can run the tests:

```bash
docker compose exec php composer behat
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

### Modified Rest-API Endpoints

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

@role
Feature: Interacting with the REST API for roles
    In order to interact with REST API for roles
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Listing of zero roles
        Given I have a "NativeCurlClient" client
        When I list all roles
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            roles
            """
        And the returned data "roles" property is an array
        And the returned data "roles" property contains "0" items

    Scenario: Listing of multiple roles
        Given I have a "NativeCurlClient" client
        And I have a role with the name "Reporter"
        And I have a role with the name "Developer"
        When I list all roles
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            roles
            """
        And the returned data "roles" property is an array
        And the returned data "roles" property contains "2" items
        And the returned data "roles.0" property contains "2" items
        And the returned data "roles.0" property contains the following data
            | property              | value                |
            | id                    | 3                    |
            | name                  | Reporter             |
        And the returned data "roles.1" property contains "2" items
        And the returned data "roles.1" property contains the following data
            | property              | value                |
            | id                    | 4                    |
            | name                  | Developer            |

    Scenario: Listing of multiple role names
        Given I have a "NativeCurlClient" client
        And I have a role with the name "Reporter"
        And I have a role with the name "Developer"
        When I list all role names
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data is an array
        And the returned data contains "2" items
        And the returned data contains the following data
            | property              | value                |
            | 3                     | Reporter             |
            | 4                     | Developer            |

    Scenario: Showing a role
        Given I have a "NativeCurlClient" client
        And I have a role with the name "Reporter"
        When I show the role with id "3"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            role
            """
        And the returned data "role" property is an array
        And the returned data "role" property has only the following properties
            """
            id
            name
            assignable
            issues_visibility
            time_entries_visibility
            users_visibility
            permissions
            """
        And the returned data "role" property contains the following data
            | property                  | value                |
            | id                        | 3                    |
            | name                      | Reporter             |
            | assignable                | false                |
            | issues_visibility         | default              |
            | time_entries_visibility   | all                  |
            | users_visibility          | all                  |
            | permissions               | []                   |

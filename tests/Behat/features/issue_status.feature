@issue_status
Feature: Interacting with the REST API for issue statuses
    In order to interact with REST API for issue statuses
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Listing of zero issue statuses
        Given I have a "NativeCurlClient" client
        When I list all issue statuses
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            issue_statuses
            """
        And the returned data "issue_statuses" property is an array
        And the returned data "issue_statuses" property contains "0" items

    Scenario: Listing of multiple issue statuses
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue status with the name "Done"
        When I list all issue statuses
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            issue_statuses
            """
        And the returned data "issue_statuses" property is an array
        And the returned data "issue_statuses" property contains "2" items
        # field 'description' was added in Redmine 5.1.0, see https://www.redmine.org/issues/2568
        And the returned data "issue_statuses.0" property contains the following data with Redmine version ">= 5.1.0"
            | property              | value                |
            | id                    | 1                    |
            | name                  | New                  |
            | is_closed             | false                |
            | description           | null                 |
        But the returned data "issue_statuses.0" property contains the following data with Redmine version "< 5.1.0"
            | property              | value                |
            | id                    | 1                    |
            | name                  | New                  |
            | is_closed             | false                |
        # field 'description' was added in Redmine 5.1.0, see https://www.redmine.org/issues/2568
        And the returned data "issue_statuses.1" property contains the following data with Redmine version ">= 5.1.0"
            | property              | value                |
            | id                    | 2                    |
            | name                  | Done                 |
            | is_closed             | false                |
            | description           | null                 |
        But the returned data "issue_statuses.1" property contains the following data with Redmine version "< 5.1.0"
            | property              | value                |
            | id                    | 2                    |
            | name                  | Done                 |
            | is_closed             | false                |

    Scenario: Listing of multiple issue status names
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue status with the name "Done"
        When I list all issue status names
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data is an array
        And the returned data contains "2" items
        And the returned data contains the following data
            | property              | value                |
            | 1                     | New                  |
            | 2                     | Done                 |

@time_entry_activity
Feature: Interacting with the REST API for time entry activities
    In order to interact with REST API for time entry activities
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Listing of zero time entry activities
        Given I have a "NativeCurlClient" client
        When I list all time entry activities
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            time_entry_activities
            """
        And the returned data "time_entry_activities" property is an array
        And the returned data "time_entry_activities" property contains "0" items

    @wip
    Scenario: Listing of multiple time entry activities
        Given I have a "NativeCurlClient" client
        And I have a time entry activiy with name "Development"
        And I have a time entry activiy with name "Support"
        When I list all time entry activities
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            time_entry_activities
            """
        And the returned data "time_entry_activities" property is an array
        And the returned data "time_entry_activities" property contains "2" items
        And the returned data "time_entry_activities.0" property is an array
        And the returned data "time_entry_activities.0" property has only the following properties
            """
            id
            name
            is_default
            active
            """
        And the returned data "time_entry_activities.0" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Development          |
            | is_default        | false                |
            | active            | true                 |
        And the returned data "time_entry_activities.1" property is an array
        And the returned data "time_entry_activities.1" property has only the following properties
            """
            id
            name
            is_default
            active
            """
        And the returned data "time_entry_activities.1" property contains the following data
            | property          | value                |
            | id                | 2                    |
            | name              | Support              |
            | is_default        | false                |
            | active            | true                 |


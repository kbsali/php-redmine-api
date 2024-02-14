Feature: Interacting with the REST API for time_entries
    In order to interact with REST API for time_entries
    As a user
    I want to make sure the Redmine server replies with the correct response

    @time_entry
    Scenario: Creating a time_entry to a project
        Given I have a "NativeCurlClient" client
        And I have a time entry activiy with name "development"
        And I create a project with name "Test Project" and identifier "test-project"
        When I create a time entry with the following data
            | property          | value                |
            | project_id        | 1                    |
            | hours             | 1                    |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            project
            user
            activity
            hours
            comments
            spent_on
            created_on
            updated_on
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 1                    |
            | hours             | 1.0                  |
            | comments          | []                   |
        And the returned data "project" property is an array
        And the returned data "project" property contains "1" items
        And the returned data "project.@attributes" property is an array
        And the returned data "project.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "project.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
        And the returned data "user" property is an array
        And the returned data "user" property contains "1" items
        And the returned data "user.@attributes" property is an array
        And the returned data "user.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "user.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Redmine Admin        |
        And the returned data "activity" property is an array
        And the returned data "activity" property contains "1" items
        And the returned data "activity.@attributes" property is an array
        And the returned data "activity.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "activity.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | development          |

Feature: Interacting with the REST API for versions
    In order to interact with REST API for versions
    As a user
    I want to make sure the Redmine server replies with the correct response

    @version
    Scenario: Creating a version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I create a version with name "Test-Version" and project identifier "test-project"
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            project
            name
            description
            status
            due_date
            sharing
            wiki_page_title
            estimated_hours
            spent_hours
            created_on
            updated_on
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test-Version         |
            | description       | []                   |
            | status            | open                 |
            | due_date          | []                   |
            | sharing           | none                 |
            | wiki_page_title   | []                   |
            | estimated_hours   | 0.0                  |
            | spent_hours       | 0.0                  |
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

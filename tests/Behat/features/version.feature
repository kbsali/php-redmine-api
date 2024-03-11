Feature: Interacting with the REST API for versions
    In order to interact with REST API for versions
    As a user
    I want to make sure the Redmine server replies with the correct response

    @version
    Scenario: Creating a version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I create a version with project identifier "test-project" with the following data
            | property          | value                |
            | name              | Test-Version         |
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

    @version
    Scenario: Updating a version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with project identifier "test-project" with the following data
            | property          | value                |
            | name              | Test-Version         |
        When I update the version with id "1" and the following data
            | property          | value                |
            | name              | New Version name     |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    @version
    Scenario: Showing a version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with name "Test-Version" and project identifier "test-project"
        When I show the version with id "1"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            version
            """
        And the returned data "version" property is an array
        And the returned data "version" property has only the following properties
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
        And the returned data "version" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test-Version         |
            | description       |                      |
            | status            | open                 |
            | due_date          | null                 |
            | sharing           | none                 |
            | wiki_page_title   | null                 |
            | estimated_hours   | 0.0                  |
            | spent_hours       | 0.0                  |

    @version @error
    Scenario: Showing a not existing version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I show the version with id "1"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

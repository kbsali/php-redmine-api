Feature: Interacting with the REST API for projects
    In order to interact with REST API for projects
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Creating a project with minimal parameters
        Given I have a "NativeCurlClient" client
        When I create a project with name "Test Project" and identifier "test-project"
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
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
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
            | identifier        | test-project         |

    Scenario: Creating a project with multiple parameters
        Given I have a "NativeCurlClient" client
        When I create a project with the following data
            | property          | value                |
            | name              | Test Project         |
            | identifier        | test-project         |
            | description       | project description  |
            | homepage          | https://example.com  |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
            | identifier        | test-project         |
            | description       | project description  |
            | homepage          | https://example.com  |
            | is_public         | true                 |
            | inherit_members   | false                |

    Scenario: Listing of zero projects
        Given I have a "NativeCurlClient" client
        And I list all projects
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            projects
            total_count
            offset
            limit
            """
        And the returned data "projects" property is an array
        And the returned data "projects" property containts "0" items
        And the returned data has proterties with the following data
            | property          | value                |
            | total_count       | 0                    |
            | offset            | 0                    |
            | limit             | 25                   |

    Scenario: Listing of one project
        Given I have a "NativeCurlClient" client
        When I create a project with name "Test Project" and identifier "test-project"
        And I list all projects
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has proterties with the following data
            | property          | value                |
            | total_count       | 1                    |
            | offset            | 0                    |
            | limit             | 25                   |
        And the returned data "projects" property is an array
        And the returned data "projects" property containts "1" items
        And the returned data "projects.0" property is an array
        # field 'homepage' was added in Redmine 5.1.0, see https://www.redmine.org/issues/39113
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

    Scenario: Updating a project
        Given I have a "NativeCurlClient" client
        When I create a project with name "Test Project" and identifier "test-project"
        And I update the project with identifier "test-project" with the following data
            | property          | value                |
            | name              | new project name     |
            | homepage          | https://example.com  |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""

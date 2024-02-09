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

    Scenario: List of all projects
        Given I have a "NativeCurlClient" client
        When I create a project with name "Test Project" and identifier "test-project"
        And I list all projects
        Then the response has the status code "200"
        And the response has the content type "application/json"

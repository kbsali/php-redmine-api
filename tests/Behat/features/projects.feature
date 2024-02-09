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
        And the returned data has the following properties
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

    Scenario: Creating a project with multiple parameters
        Given I have a "NativeCurlClient" client
        When I create a project with name "Test Project", identifier "test-project" and the following data
            | key          | value                |
            | description  | project description  |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has the following properties
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

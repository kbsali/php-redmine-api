Feature: Interacting with the REST API for memberships
    In order to interact with REST API for memberships
    As a user
    I want to make sure the Redmine server replies with the correct response

    @membership
    Scenario: Creating a membership
        Given I have a "NativeCurlClient" client
        And I have a role with the name "Developer"
        And I create a project with name "Test Project" and identifier "test-project"
        When I create a membership to project with identifier "test-project" and the following data
            | property          | value                |
            | user_id           | 1                    |
            | role_ids          | [3]                  |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"

    @membership
    Scenario: Updating a membership
        Given I have a "NativeCurlClient" client
        And I have a role with the name "Developer"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a membership to project with identifier "test-project" and the following data
            | property          | value                |
            | user_id           | 1                    |
            | role_ids          | [3]                  |
        When I update the membership with id "1" and the following data
            | property          | value                |
            | user_id           | 1                    |
            | role_ids          | [3]                  |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

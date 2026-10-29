@issue_relation
Feature: Interacting with the REST API for issue relations
    In order to interact with REST API for issue relations
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Creating an issue relation with miminal data
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | first issue          |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        And I create an issue with the following data
            | property          | value                |
            | subject           | second issue         |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I create an issue relation for issue id "1" with the following data
            | property          | value                |
            | issue_to_id       | 2                    |
        Then the response has the status code "201"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            relation
            """
        And the returned data "relation" property is an array
        And the returned data "relation" property has only the following properties
            """
            id
            issue_id
            issue_to_id
            relation_type
            delay
            """
        And the returned data "relation" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | issue_id          | 1                    |
            | issue_to_id       | 2                    |
            | relation_type     | relates              |
            | delay             | null                 |

    Scenario: Showing an issue relation
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | first issue          |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        And I create an issue with the following data
            | property          | value                |
            | subject           | second issue         |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        And I create an issue relation for issue id "1" with the following data
            | property          | value                |
            | issue_to_id       | 2                    |
        When I show the issue relation with the id "1"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the response has the content
            """
            {"relation":{"id":1,"issue_id":1,"issue_to_id":2,"relation_type":"relates","delay":null}}
            """
        And the returned data has only the following properties
            """
            id
            issue_id
            issue_to_id
            relation_type
            delay
            """
        And the returned data contains the following data
            | property          | value                |
            | id                | 1                    |
            | issue_id          | 1                    |
            | issue_to_id       | 2                    |
            | relation_type     | relates              |
            | delay             | null                 |

    Scenario: Deleting an issue relation
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | first issue          |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        And I create an issue with the following data
            | property          | value                |
            | subject           | second issue         |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        And I create an issue relation for issue id "1" with the following data
            | property          | value                |
            | issue_to_id       | 2                    |
        When I delete the issue relation with the id "1"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    @error
    Scenario: Showing a not existing issue relation
        Given I have a "NativeCurlClient" client
        When I show the issue relation with the id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Showing a not existing issue relation without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I show the issue relation with the id "10"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is an array
        And the returned data contains "0" items

    @error
    Scenario: Try to create an issue relation with invalid data
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | first issue          |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I create an issue relation for issue id "1" with the following data
            | property          | value                |
            | issue_to_id       | 1                    |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "422"
        And the response has the content type "application/json"
        And the response has the content
            """
            {"errors":["Related issue is invalid"]}
            """

    @error @without-future-mode
    Scenario: Try to create an issue relation with invalid data without future mode
        Given I have a "NativeCurlClient" client without future mode
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | first issue          |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I create an issue relation for issue id "1" with the following data
            | property          | value                |
            | issue_to_id       | 1                    |
        Then the response has the status code "422"
        And the response has the content type "application/json"
        And the response has the content
            """
            {"errors":["Related issue is invalid"]}
            """
        And the returned data has only the following properties
            """
            errors
            """
        And the returned data "errors" property contains "1" items

    @error
    Scenario: Try to create an issue relation for a non-existing issue
        Given I have a "NativeCurlClient" client
        When I create an issue relation for issue id "10" with the following data
            | property          | value                |
            | issue_to_id       | 2                    |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""

    @error
    Scenario: Try to delete a non-existing issue relation
        Given I have a "NativeCurlClient" client
        When I delete the issue relation with the id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Try to delete a non-existing issue relation without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I delete the issue relation with the id "10"
        Then the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

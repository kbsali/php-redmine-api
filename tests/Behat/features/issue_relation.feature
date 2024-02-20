Feature: Interacting with the REST API for issue relations
    In order to interact with REST API for issue relations
    As a user
    I want to make sure the Redmine server replies with the correct response

    @issue_relation
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

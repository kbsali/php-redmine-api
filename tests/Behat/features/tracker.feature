@tracker
Feature: Interacting with the REST API for trackers
    In order to interact with REST API for trackers
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Listing of zero trackers
        Given I have a "NativeCurlClient" client
        When I list all trackers
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            trackers
            """
        And the returned data "trackers" property is an array
        And the returned data "trackers" property contains "0" items

    Scenario: Listing of multiple trackers
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have a tracker with the name "Feature" and default status id "1"
        And I have a tracker with the name "Defect" and default status id "1"
        When I list all trackers
        Then the response has the status code "200"
        And the response has the content type "application/json"
         And the returned data has only the following properties
            """
            trackers
            """
        And the returned data "trackers" property is an array
        And the returned data "trackers" property contains "2" items
        And the returned data "trackers.0" property is an array
        And the returned data "trackers.0" property has only the following properties with Redmine version ">= 5.0.0"
            """
            id
            name
            default_status
            description
            enabled_standard_fields
            """
        But the returned data "trackers.0" property has only the following properties with Redmine version "< 5.0.0"
            """
            id
            name
            default_status
            description
            """
        And the returned data "trackers.0" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Feature              |
            | description       | null                 |
        And the returned data "trackers.0.default_status" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | New                  |
        And the returned data "trackers.0.enabled_standard_fields" property contains the following data with Redmine version ">= 5.1.0"
            | property          | value                |
            | 0                 | assigned_to_id       |
            | 1                 | category_id          |
            | 2                 | fixed_version_id     |
            | 3                 | parent_issue_id      |
            | 4                 | start_date           |
            | 5                 | due_date             |
            | 6                 | estimated_hours      |
            | 7                 | done_ratio           |
            | 8                 | description          |
            | 9                 | priority_id          |
        But the returned data "trackers.0.enabled_standard_fields" property contains the following data with Redmine version ">= 5.0.0"
            | property          | value                |
            | 0                 | assigned_to_id       |
            | 1                 | category_id          |
            | 2                 | fixed_version_id     |
            | 3                 | parent_issue_id      |
            | 4                 | start_date           |
            | 5                 | due_date             |
            | 6                 | estimated_hours      |
            | 7                 | done_ratio           |
            | 8                 | description          |

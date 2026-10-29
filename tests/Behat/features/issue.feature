@issue
Feature: Interacting with the REST API for issues
    In order to interact with REST API for issues
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Creating an issue with miminal data
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        When I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            created_on
            updated_on
            closed_on
            """
        And the returned data has proterties with the following data
            | property              | value                |
            | id                    | 1                    |
            | subject               | issue subject        |
            | description           | []                   |
            | due_date              | []                   |
            | done_ratio            | 0                    |
            | is_private            | false                |
            | estimated_hours       | []                   |
            | total_estimated_hours | []                   |
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
        And the returned data "tracker" property is an array
        And the returned data "tracker" property contains "1" items
        And the returned data "tracker.@attributes" property is an array
        And the returned data "tracker.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "tracker.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Defect               |
        And the returned data "status" property is an array
        And the returned data "status" property contains "1" items
        And the returned data "status.@attributes" property is an array
        And the returned data "status.@attributes" property has only the following properties
            """
            id
            name
            is_closed
            """
        And the returned data "status.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | New                  |
            | is_closed         | false                |
        And the returned data "priority" property is an array
        And the returned data "priority" property contains "1" items
        And the returned data "priority.@attributes" property is an array
        And the returned data "priority.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "priority.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Normal               |
        And the returned data "author" property is an array
        And the returned data "author" property contains "1" items
        And the returned data "author.@attributes" property is an array
        And the returned data "author.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "author.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Redmine Admin        |

    @custom_field
    Scenario: Creating an issue with custom field
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a custom field for issues with the name "Note"
        And I enable the tracker with ID "1" for custom field with ID "1"
        When I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
            | custom_fields     | [{"id":1,"value":"Note for custom field"}] |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            custom_fields
            created_on
            updated_on
            closed_on
            """
        And the returned data has proterties with the following data
            | property              | value                |
            | id                    | 1                    |
            | subject               | issue subject        |
            | description           | []                   |
            | due_date              | []                   |
            | done_ratio            | 0                    |
            | is_private            | false                |
            | estimated_hours       | []                   |
            | total_estimated_hours | []                   |
        And the returned data "custom_fields.custom_field" property has only the following properties
            """
            @attributes
            value
            """
        And the returned data "custom_fields.custom_field.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Note                 |
        And the returned data "custom_fields.custom_field" property contains the following data
            | property          | value                |
            | value             | Note for custom field |

    Scenario: Updating an issue
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | subject           | new issue subject    |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    Scenario: Unassigning an issue
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with name "v1" and project identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
            | assigned_to_id    | 1                    |
            | fixed_version_id  | 1                    |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | assigned_to_id    |                      |
            | fixed_version_id  |                      |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""
        When I show the issue with id "1"
        Then the response has the status code "200"
        And the returned data "issue" property has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            spent_hours
            total_spent_hours
            created_on
            updated_on
            closed_on
            """

    @without-future-mode
    Scenario: Unassigning an issue without future mode has no effect on the assigned user
        Given I have a "NativeCurlClient" client without future mode
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with name "v1" and project identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
            | assigned_to_id    | 1                    |
            | fixed_version_id  | 1                    |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | assigned_to_id    |                      |
            | fixed_version_id  |                      |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""
        When I show the issue with id "1"
        Then the response has the status code "200"
        And the returned data "issue" property has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            assigned_to
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            spent_hours
            total_spent_hours
            created_on
            updated_on
            closed_on
            """

    Scenario: Unassigning an issue and updating other data in one request
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with name "v1" and project identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
            | assigned_to_id    | 1                    |
            | fixed_version_id  | 1                    |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | subject           | new issue subject    |
            | notes             | unassigned           |
            | assigned_to_id    |                      |
            | fixed_version_id  |                      |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""
        When I show the issue with id "1"
        Then the response has the status code "200"
        And the returned data "issue" property has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            spent_hours
            total_spent_hours
            created_on
            updated_on
            closed_on
            """
        And the returned data "issue" property contains the following data
            | property          | value                |
            | subject           | new issue subject    |

    @custom_field
    Scenario: Unassigning an issue and updating a custom field in one request
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with name "v1" and project identifier "test-project"
        And I create a custom field for issues with the name "Note"
        And I enable the tracker with ID "1" for custom field with ID "1"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
            | assigned_to_id    | 1                    |
            | fixed_version_id  | 1                    |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | assigned_to_id    |                      |
            | custom_fields     | [{"id":1,"value":"Note for custom field"}] |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""
        When I show the issue with id "1"
        Then the response has the status code "200"
        And the returned data "issue" property has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            fixed_version
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            spent_hours
            total_spent_hours
            custom_fields
            created_on
            updated_on
            closed_on
            """
        And the returned data "issue" property contains the following data
            | property          | value                |
            | subject           | issue subject        |
        And the returned data "issue.custom_fields.0" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Note                 |
            | value             | Note for custom field |

    Scenario: Showing an issue
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I show the issue with id "1"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            issue
            """
        And the returned data "issue" property is an array
        And the returned data "issue" property has only the following properties
            """
            id
            project
            tracker
            status
            priority
            author
            subject
            description
            start_date
            due_date
            done_ratio
            is_private
            estimated_hours
            total_estimated_hours
            spent_hours
            total_spent_hours
            created_on
            updated_on
            closed_on
            """
        And the returned data "issue" property contains the following data
            | property              | value                |
            | id                    | 1                    |
            | subject               | issue subject        |
            | description           | null                 |
            | due_date              | null                 |
            | done_ratio            | 0                    |
            | is_private            | false                |
            | estimated_hours       | null                 |
            | total_estimated_hours | null                 |
            | spent_hours           | 0.0                  |
            | total_spent_hours     | 0.0                  |
            | closed_on             | null                 |
        And the returned data "issue.project" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
        And the returned data "issue.tracker" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Defect               |
        And the returned data "issue.status" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | New                  |
            | is_closed         | false                |
        And the returned data "issue.priority" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Normal               |
        And the returned data "issue.author" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Redmine Admin        |

    @error
    Scenario: Showing a not existing issue
        Given I have a "NativeCurlClient" client
        When I show the issue with id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Showing a not existing issue without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I show the issue with id "10"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

    @error
    Scenario: Listing issues with an invalid filter
        Given I have a "NativeCurlClient" client
        When I list all issues with the following parameters
            | property          | value                |
            | created_on        | invalid              |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "422"
        And the response has the content type "application/json"
        And the response has the content
            """
            {"errors":["Created is invalid"]}
            """

    @error @without-future-mode
    Scenario: Listing issues with an invalid filter without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I list all issues with the following parameters
            | property          | value                |
            | created_on        | invalid              |
        Then the response has the status code "422"
        And the response has the content type "application/json"
        And the response has the content
            """
            {"errors":["Created is invalid"]}
            """
        And the returned data has only the following properties
            """
            errors
            """
        And the returned data "errors" property contains "1" items

    Scenario: Adding a watcher to an issue
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I add the user id "1" as a watcher to the issue with id "1"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    Scenario: Removing a watcher from an issue
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        And I add the user id "1" as a watcher to the issue with id "1"
        When I remove the user id "1" as a watcher from the issue with id "1"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    Scenario: Removing an issue
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I remove the issue with id "1"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    @error
    Scenario: Creating an issue with invalid data
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        When I create an issue with the following data
            | property          | value                |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "422"
        And the response has the content type "application/xml"
        And the response has the content
            """
            <?xml version="1.0" encoding="UTF-8"?><errors type="array"><error>Subject cannot be blank</error></errors>
            """

    @error
    Scenario: Updating an issue with invalid data
        Given I have a "NativeCurlClient" client
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | start_date        | 2026-10-02           |
            | due_date          | 2026-10-01           |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "422"
        And the response has the content type "application/xml"
        And the response has the content
            """
            <?xml version="1.0" encoding="UTF-8"?><errors type="array"><error>Due date must be greater than start date</error></errors>
            """

    @error
    Scenario: Updating a not existing issue
        Given I have a "NativeCurlClient" client
        When I update the issue with id "10" and the following data
            | property          | value                |
            | subject           | new issue subject    |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error
    Scenario: Adding a watcher to a not existing issue
        Given I have a "NativeCurlClient" client
        When I add the user id "1" as a watcher to the issue with id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "403"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error
    Scenario: Removing a watcher from a not existing issue
        Given I have a "NativeCurlClient" client
        When I remove the user id "1" as a watcher from the issue with id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "403"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error
    Scenario: Removing a not existing issue
        Given I have a "NativeCurlClient" client
        When I remove the issue with id "10"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Creating an issue with invalid data without future mode
        Given I have a "NativeCurlClient" client without future mode
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        When I create an issue with the following data
            | property          | value                |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        Then the response has the status code "422"
        And the response has the content type "application/xml"
        And the response has the content
            """
            <?xml version="1.0" encoding="UTF-8"?><errors type="array"><error>Subject cannot be blank</error></errors>
            """
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            @attributes
            error
            """

    @error @without-future-mode
    Scenario: Updating an issue with invalid data without future mode
        Given I have a "NativeCurlClient" client without future mode
        And I have an issue status with the name "New"
        And I have an issue priority with the name "Normal"
        And I have a tracker with the name "Defect" and default status id "1"
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue with the following data
            | property          | value                |
            | subject           | issue subject        |
            | project           | Test Project         |
            | tracker           | Defect               |
            | priority          | Normal               |
            | status            | New                  |
        When I update the issue with id "1" and the following data
            | property          | value                |
            | start_date        | 2026-10-02           |
            | due_date          | 2026-10-01           |
        Then the response has the status code "422"
        And the response has the content type "application/xml"
        And the response has the content
            """
            <?xml version="1.0" encoding="UTF-8"?><errors type="array"><error>Due date must be greater than start date</error></errors>
            """
        And the returned data is exactly
            """
            <?xml version="1.0" encoding="UTF-8"?><errors type="array"><error>Due date must be greater than start date</error></errors>
            """

    @error @without-future-mode
    Scenario: Updating a not existing issue without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I update the issue with id "10" and the following data
            | property          | value                |
            | subject           | new issue subject    |
        Then the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

    @error @without-future-mode
    Scenario: Adding a watcher to a not existing issue without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I add the user id "1" as a watcher to the issue with id "10"
        Then the response has the status code "403"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

    @error @without-future-mode
    Scenario: Removing a watcher from a not existing issue without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I remove the user id "1" as a watcher from the issue with id "10"
        Then the response has the status code "403"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

    @error @without-future-mode
    Scenario: Removing a not existing issue without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I remove the issue with id "10"
        Then the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

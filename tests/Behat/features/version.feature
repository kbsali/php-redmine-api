@version
Feature: Interacting with the REST API for versions
    In order to interact with REST API for versions
    As a user
    I want to make sure the Redmine server replies with the correct response

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

    @error
    Scenario: Showing a not existing version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I show the version with id "1"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

    Scenario: Listing of zero versions
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I list all versions for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            versions
            total_count
            """
        And the returned data contains the following data
            | property          | value                |
            | versions          | []                   |
            | total_count       | 0                    |

    Scenario: Listing of multiple versions
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with name "Test-Version B" and project identifier "test-project"
        And I create a version with name "Test-Version A" and project identifier "test-project"
        When I list all versions for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            versions
            total_count
            """
        And the returned data contains the following data
            | property          | value                |
            | total_count       | 2                    |
        And the returned data "versions" property is an array
        And the returned data "versions" property contains "2" items
        And the returned data "versions.0" property is an array
        And the returned data "versions.0" property has only the following properties
            """
            id
            project
            name
            description
            status
            due_date
            sharing
            wiki_page_title
            created_on
            updated_on
            """
        And the returned data "versions.0" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test-Version B       |
            | description       |                      |
            | status            | open                 |
            | sharing           | none                 |
            | wiki_page_title   | null                 |
        And the returned data "versions.0.project" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |

    @wip
    Scenario: Listing of multiple version names
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project 1" and identifier "test-project-1"
        And I create a project with name "Test Project 2" and identifier "test-project-2"
        And I create a version with name "Test-Version 1B" and project identifier "test-project-1"
        And I create a version with name "Test-Version 1A" and project identifier "test-project-1"
        And I create a version with name "Test-Version 2B" and project identifier "test-project-2"
        And I create a version with name "Test-Version 2A" and project identifier "test-project-2"
        When I list all version names for project identifier "test-project-2"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data contains "2" items
        And the returned data contains the following data
            | property          | value                |
            | 3                 | Test-Version 2B      |
            | 4                 | Test-Version 2A      |

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

    Scenario: Removing a version
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a version with project identifier "test-project" with the following data
            | property          | value                |
            | name              | Test-Version         |
        When I remove the version with id "1"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

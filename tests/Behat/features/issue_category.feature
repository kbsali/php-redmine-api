Feature: Interacting with the REST API for issue categories
    In order to interact with REST API for issue categories
    As a user
    I want to make sure the Redmine server replies with the correct response

    @issue_category
    Scenario: Creating an issue category with miminal data
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name        |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            project
            name
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Category name        |
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

    @issue_category
    Scenario: Creating an issue category with all data
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name        |
            | assigned_to_id    | 1                    |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            project
            name
            assigned_to
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Category name        |
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
        And the returned data "assigned_to" property is an array
        And the returned data "assigned_to" property contains "1" items
        And the returned data "assigned_to.@attributes" property is an array
        And the returned data "assigned_to.@attributes" property has only the following properties
            """
            id
            name
            """
        And the returned data "assigned_to.@attributes" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Redmine Admin        |

    Scenario: Listing of zero issue categories
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I list all issue categories for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            issue_categories
            total_count
            """
        And the returned data contains the following data
            | property          | value                |
            | issue_categories  | []                   |
            | total_count       | 0                    |

    Scenario: Listing of multiple issue categories
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name B      |
        And I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name A      |
        When I list all issue categories for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            issue_categories
            total_count
            """
        And the returned data contains the following data
            | property          | value                |
            | total_count       | 2                    |
        And the returned data "issue_categories" property is an array
        And the returned data "issue_categories" property contains "2" items
        And the returned data "issue_categories.0" property is an array
        And the returned data "issue_categories.0" property has only the following properties
            """
            id
            project
            name
            """
        And the returned data "issue_categories.0" property contains the following data
            | property          | value                |
            | id                | 2                    |
            | name              | Category name A       |
        And the returned data "issue_categories.0.project" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
        And the returned data "issue_categories.1" property is an array
        And the returned data "issue_categories.1" property has only the following properties
            """
            id
            project
            name
            """
        And the returned data "issue_categories.1" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Category name B      |
        And the returned data "issue_categories.1.project" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |

    Scenario: Listing of multiple issue category names
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name B      |
        And I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name A      |
        When I list all issue category names for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data contains the following data
            | property          | value                |
            | 1                 | Category name B      |
            | 2                 | Category name A      |

    @issue_category
    Scenario: Updating an issue category with all data
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name        |
            | assigned_to_id    | 1                    |
        When I update the issue category with id "1" and the following data
            | property          | value                |
            | name              | New category name    |
            | assigned_to_id    | 1                    |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    @issue_category
    Scenario: Deleting an issue category
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create an issue category for project identifier "test-project" and with the following data
            | property          | value                |
            | name              | Category name        |
            | assigned_to_id    | 1                    |
        When I remove the issue category with id "1"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

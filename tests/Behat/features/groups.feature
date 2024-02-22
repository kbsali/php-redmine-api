Feature: Interacting with the REST API for groups
    In order to interact with REST API for groups
    As a user
    I want to make sure the Redmine server replies with the correct response

    @group
    Scenario: Creating a group with minimal parameters
        Given I have a "NativeCurlClient" client
        When I create a group with name "Test Group"
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            name
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 4                    |
            | name              | Test Group           |

    @group
    Scenario: Listing of zero groups
        Given I have a "NativeCurlClient" client
        When I list all groups
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            groups
            """
        And the returned data "groups" property is an array
        And the returned data "groups" property contains "0" items

    @group
    Scenario: Listing of one group
        Given I have a "NativeCurlClient" client
        And I create a group with name "Test Group"
        When I list all groups
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            groups
            """
        And the returned data "groups" property is an array
        And the returned data "groups" property contains "1" items
        And the returned data "groups.0" property is an array
        And the returned data "groups.0" property has only the following properties
            """
            id
            name
            """
        And the returned data "groups.0" property contains the following data
            | property          | value                |
            | id                | 4                    |
            | name              | Test Group           |

    @group
    Scenario: Showing a specific group
        Given I have a "NativeCurlClient" client
        And I create a group with name "Test Group"
        When I show the group with id "4"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            group
            """
        And the returned data "group" property is an array
        And the returned data "group" property has only the following properties
            """
            id
            name
            """
        And the returned data "group" property contains the following data
            | property          | value                |
            | id                | 4                    |
            | name              | Test Group           |

    @group @error
    Scenario: Try to show a non-existing group
        Given I have a "NativeCurlClient" client
        When I show the group with id "40"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

    @group
    Scenario: Updating a group
        Given I have a "NativeCurlClient" client
        And I create a group with the following data
            | property          | value                |
            | name              | Test Group           |
        When I update the group with id "4" with the following data
            | property          | value                |
            | name              | new group name       |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

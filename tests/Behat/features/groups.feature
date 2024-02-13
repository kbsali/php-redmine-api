Feature: Interacting with the REST API for groups
    In order to interact with REST API for groups
    As a user
    I want to make sure the Redmine server replies with the correct response

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
        And the returned data "groups" property containts "0" items

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
        And the returned data "groups" property containts "1" items
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

    @wip
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
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

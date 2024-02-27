Feature: Interacting with the REST API for users
    In order to interact with REST API for users
    As a user
    I want to make sure the Redmine server replies with the correct response

    @user
    Scenario: Creating a user
        Given I have a "NativeCurlClient" client
        When I create a user with the following data
            | property          | value                |
            | login             | username             |
            | firstname         | first                |
            | lastname          | last                 |
            | mail              | mail@example.com     |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            id
            login
            admin
            firstname
            lastname
            mail
            created_on
            updated_on
            last_login_on
            passwd_changed_on
            twofa_scheme
            api_key
            status
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | id                | 4                    |
            | login             | username             |
            | admin             | false                |
            | firstname         | first                |
            | lastname          | last                 |
            | mail              | mail@example.com     |
            | last_login_on     | []                   |
            | passwd_changed_on | []                   |
            | twofa_scheme      | []                   |
            | status            | 1                    |

    @user
    Scenario: Showing a user
        Given I have a "NativeCurlClient" client
        When I show the user with id "1"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            user
            """
        And the returned data "user" property is an array
        And the returned data "user" property has only the following properties
            """
            id
            login
            admin
            firstname
            lastname
            mail
            created_on
            updated_on
            last_login_on
            passwd_changed_on
            twofa_scheme
            api_key
            status
            groups
            memberships
            """
        And the returned data "user" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | login             | admin                |
            | admin             | true                 |
            | firstname         | Redmine              |
            | lastname          | Admin                |
            | mail              | admin@example.net    |
            | twofa_scheme      | null                 |
            | status            | 1                    |
            | groups            | []                   |
            | memberships       | []                   |

    @user @error
    Scenario: Showing a not existing user
        Given I have a "NativeCurlClient" client
        When I show the user with id "10"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

Feature: Interacting with the REST API for users
    In order to interact with REST API for users
    As a user
    I want to make sure the Redmine server replies with the correct response

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
            firstname
            lastname
            created_on
            updated_on
            last_login_on
            passwd_changed_on
            memberships
            """
        And the returned data "user" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | login             | admin                |
            | firstname         | Redmine              |
            | lastname          | Admin                |
            | memberships       | []                   |

    @user @error
    Scenario: Showing a not existing user
        Given I have a "NativeCurlClient" client
        When I show the user with id "10"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

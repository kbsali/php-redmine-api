Feature: Interacting with the REST API for wikis
    In order to interact with REST API for wiki
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Creating a wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        Then the response has the status code "201"
        And the response has the content type "application/xml"
        And the returned data is an instance of "SimpleXMLElement"
        And the returned data has only the following properties
            """
            title
            text
            version
            author
            comments
            created_on
            updated_on
            """
        And the returned data has proterties with the following data
            | property          | value                |
            | title             | Test+Page            |
            | text              | # My first wiki page |
            | version           | 1                    |
            | comments          | []                   |
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

    Scenario: Showing a wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        When I show the wiki page with name "Test Page" and project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            wiki_page
            """
        And the returned data "wiki_page" property is an array
        And the returned data "wiki_page" property has only the following properties
            """
            title
            text
            version
            author
            comments
            created_on
            updated_on
            attachments
            """
        And the returned data "wiki_page" property contains the following data
            | property          | value                |
            | title             | Test+Page            |
            | text              | # My first wiki page |
            | version           | 1                    |
            | comments          | null                 |
            | attachments       | []                   |
        And the returned data "wiki_page.author" property is an array
        And the returned data "wiki_page.author" property has only the following properties
            """
            id
            name
            """
        And the returned data "wiki_page.author" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Redmine Admin        |

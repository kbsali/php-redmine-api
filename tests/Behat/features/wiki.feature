Feature: Interacting with the REST API for wikis
    In order to interact with REST API for wiki
    As a user
    I want to make sure the Redmine server replies with the correct response

    @wiki
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

    @wiki @attachment
    Scenario: Creating a wiki page with attachment upload
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I upload the content of the file "%tests_dir%/Fixtures/testfile_01.txt" with the following data
            | property          | value                |
            | filename          | testfile.txt         |
        When I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
            | uploads           | [{"token":"1.7b962f8af22e26802b87abfa0b07b21dbd03b984ec8d6888dabd3f69cff162f8","filename":"filename.txt","content-type":"text/plain"}] |
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

    @wiki
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

    @wiki @error
    Scenario: Try to show a not existing wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I show the wiki page with name "This-page-does-not-exist" and project identifier "test-project"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

    @wiki @attachment
    Scenario: Showing a wiki page with uploaded attachment
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I upload the content of the file "%tests_dir%/Fixtures/testfile_01.txt" with the following data
            | property          | value                |
            | filename          | testfile.txt         |
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
            | uploads           | [{"token":"1.7b962f8af22e26802b87abfa0b07b21dbd03b984ec8d6888dabd3f69cff162f8","filename":"filename.txt","content-type":"text/plain"}] |
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
        And the returned data "wiki_page.attachments" property is an array
        And the returned data "wiki_page.attachments" property contains "1" items
        And the returned data "wiki_page.attachments.0" property is an array
        And the returned data "wiki_page.attachments.0" property has only the following properties
            """
            id
            filename
            filesize
            content_type
            description
            content_url
            author
            created_on
            """
        And the returned data "wiki_page.attachments.0" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | filename          | filename.txt         |
            | filesize          | 65                   |
            | content_type      | text/plain           |
            | description       |                      |
            | content_url       | http://redmine-%redmine_id%:3000/attachments/download/1/filename.txt |
        And the returned data "wiki_page.attachments.0.author" property is an array
        And the returned data "wiki_page.attachments.0.author" property has only the following properties
            """
            id
            name
            """
        And the returned data "wiki_page.attachments.0.author" property contains the following data
            | property          | value                |
            | id                | 1                    |
            | name              | Redmine Admin        |

    @wiki
    Scenario: Updating a wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        When I update the wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                          |
            | text              | # First Wiki page with changes |
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    @wiki
    Scenario: Deleting a wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        When I delete the wiki page with name "Test Page" and project identifier "test-project"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

@wiki
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
        And the returned data has only the following properties with Redmine version ">= 7.0.0"
            """
            title
            text
            version
            author
            comments
            project
            created_on
            updated_on
            """
        But the returned data has only the following properties with Redmine version "< 7.0.0"
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
            | title             | Test_Page            |
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

    @attachment
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
        And the returned data has only the following properties with Redmine version ">= 7.0.0"
            """
            title
            text
            version
            author
            comments
            project
            created_on
            updated_on
            """
        But the returned data has only the following properties with Redmine version "< 7.0.0"
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
            | title             | Test_Page            |
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
        And the returned data "wiki_page" property has only the following properties with Redmine version ">= 7.0.0"
            """
            title
            text
            version
            author
            comments
            project
            created_on
            updated_on
            attachments
            """
        But the returned data "wiki_page" property has only the following properties with Redmine version "< 7.0.0"
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
            | title             | Test_Page            |
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
        And the returned data "wiki_page.project" property contains the following data with Redmine version ">= 7.0.0"
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |

    @error
    Scenario: Try to show a not existing wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I show the wiki page with name "This-page-does-not-exist" and project identifier "test-project"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Try to show a not existing wiki page without future mode
        Given I have a "NativeCurlClient" client without future mode
        And I create a project with name "Test Project" and identifier "test-project"
        When I show the wiki page with name "This-page-does-not-exist" and project identifier "test-project"
        Then the response has the status code "404"
        And the response has the content type "application/json"
        And the response has the content ""
        And the returned data is false

    @attachment
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
        And the returned data "wiki_page" property has only the following properties with Redmine version ">= 7.0.0"
            """
            title
            text
            version
            author
            comments
            project
            created_on
            updated_on
            attachments
            """
        But the returned data "wiki_page" property has only the following properties with Redmine version "< 7.0.0"
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
            | title             | Test_Page            |
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
        And the returned data "wiki_page.project" property contains the following data with Redmine version ">= 7.0.0"
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
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
            | content_url       | %redmine_base_url%/attachments/download/1/filename.txt |
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

    Scenario: Listing of zero wiki pages
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I list all wiki pages for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            wiki_pages
            """
        And the returned data contains the following data
            | property          | value                |
            | wiki_pages        | []                   |

    Scenario: Listing of multiple wiki pages
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Parent" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # Parent page        |
        And I create a wiki page with name "Child" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # Child page         |
            | parent_title      | Parent               |
        When I list all wiki pages for project identifier "test-project"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            wiki_pages
            """
        And the returned data "wiki_pages" property is an array
        And the returned data "wiki_pages" property contains "2" items
        And the returned data "wiki_pages.0" property is an array
        And the returned data "wiki_pages.0" property has only the following properties with Redmine version ">= 7.0.1"
            """
            title
            parent
            version
            project
            created_on
            updated_on
            """
        But the returned data "wiki_pages.0" property has only the following properties with Redmine version "< 7.0.1"
            """
            title
            parent
            version
            created_on
            updated_on
            """
        And the returned data "wiki_pages.0" property contains the following data
            | property          | value                |
            | title             | Child                |
            | version           | 1                    |
        And the returned data "wiki_pages.0.parent" property contains the following data
            | property          | value                |
            | title             | Parent               |
        And the returned data "wiki_pages.0.project" property contains the following data with Redmine version ">= 7.0.1"
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |
        And the returned data "wiki_pages.1" property is an array
        And the returned data "wiki_pages.1" property has only the following properties with Redmine version ">= 7.0.1"
            """
            title
            version
            project
            created_on
            updated_on
            """
        But the returned data "wiki_pages.1" property has only the following properties with Redmine version "< 7.0.1"
            """
            title
            version
            created_on
            updated_on
            """
        And the returned data "wiki_pages.1" property contains the following data
            | property          | value                |
            | title             | Parent               |
            | version           | 1                    |
        And the returned data "wiki_pages.1.project" property contains the following data with Redmine version ">= 7.0.1"
            | property          | value                |
            | id                | 1                    |
            | name              | Test Project         |

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

    Scenario: Removing a wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        When I remove the wiki page with name "Test Page" and project identifier "test-project"
        Then the response has the status code "204"
        And the response has an empty content type
        And the response has the content ""
        And the returned data is exactly ""

    @error
    Scenario: Try to create a wiki page for a non-existing project
        Given I have a "NativeCurlClient" client
        When I create a wiki page with name "Test Page" and project identifier "not-existing" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Try to create a wiki page for a non-existing project without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I create a wiki page with name "Test Page" and project identifier "not-existing" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        Then the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

    @error
    Scenario: Try to update a wiki page with a version conflict
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        When I update the wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # Changed text       |
            | version           | 5                    |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "409"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Try to update a wiki page with a version conflict without future mode
        Given I have a "NativeCurlClient" client without future mode
        And I create a project with name "Test Project" and identifier "test-project"
        And I create a wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # My first wiki page |
        When I update the wiki page with name "Test Page" and project identifier "test-project" with the following data
            | property          | value                |
            | text              | # Changed text       |
            | version           | 5                    |
        Then the response has the status code "409"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

    @error
    Scenario: Try to update a wiki page of a non-existing project
        Given I have a "NativeCurlClient" client
        When I update the wiki page with name "Test Page" and project identifier "not-existing" with the following data
            | property          | value                |
            | text              | # Changed text       |
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Try to update a wiki page of a non-existing project without future mode
        Given I have a "NativeCurlClient" client without future mode
        When I update the wiki page with name "Test Page" and project identifier "not-existing" with the following data
            | property          | value                |
            | text              | # Changed text       |
        Then the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

    @error
    Scenario: Try to remove a non-existing wiki page
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
        When I remove the wiki page with name "Not Existing" and project identifier "test-project"
        Then an UnexpectedResponseException was thrown
        And the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""

    @error @without-future-mode
    Scenario: Try to remove a non-existing wiki page without future mode
        Given I have a "NativeCurlClient" client without future mode
        And I create a project with name "Test Project" and identifier "test-project"
        When I remove the wiki page with name "Not Existing" and project identifier "test-project"
        Then the response has the status code "404"
        And the response has the content type "application/xml"
        And the response has the content ""
        And the returned data is exactly ""

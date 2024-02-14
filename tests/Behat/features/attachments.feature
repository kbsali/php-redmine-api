Feature: Interacting with the REST API for attachments
    In order to interact with REST API for attachments
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Uploading an attachment
        Given I have a "NativeCurlClient" client
        When I upload the content of the file "%tests_dir%/Fixtures/testfile_01.txt" with the following data
            | property          | value                |
            | filename          | testfile.txt         |
        Then the response has the status code "201"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            upload
            """
        And the returned data "upload" property is an array
        And the returned data "upload" property has only the following properties
            """
            id
            token
            """
        And the returned data "upload" property contains the following data
            | property          | value                                                              |
            | id                | 1                                                                  |
            | token             | 1.7b962f8af22e26802b87abfa0b07b21dbd03b984ec8d6888dabd3f69cff162f8 |

    Scenario: Showing the details of an attachment
        Given I have a "NativeCurlClient" client
        And I upload the content of the file "%tests_dir%/Fixtures/testfile_01.txt" with the following data
            | property          | value                |
            | filename          | testfile.txt         |
        When I show the attachment with the id "1"
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            attachment
            """
        And the returned data "attachment" property is an array
        And the returned data "attachment" property has only the following properties
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
        And the returned data "attachment" property contains the following data
            | property          | value                                                                |
            | id                | 1                                                                    |
            | filename          | testfile.txt                                                         |
            | filesize          | 65                                                                   |
            | content_type      | text/plain                                                           |
            | description       | null                                                                 |
            | content_url       | http://redmine-%redmine_id%:3000/attachments/download/1/testfile.txt |
        And the returned data "attachment.author" property is an array
        And the returned data "attachment.author" property contains the following data
            | property          | value                                                                |
            | id                | 1                                                                    |
            | name              | Redmine Admin                                                        |
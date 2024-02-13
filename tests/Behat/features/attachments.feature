Feature: Interacting with the REST API for attachments
    In order to interact with REST API for attachments
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Uploading an attachment
        Given I have a "NativeCurlClient" client
        And I create a project with name "Test Project" and identifier "test-project"
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

@custom_field
Feature: Interacting with the REST API for custom fields
    In order to interact with REST API for custom fields
    As a user
    I want to make sure the Redmine server replies with the correct response

    Scenario: Listing of zero custom fields
        Given I have a "NativeCurlClient" client
        When I list all custom fields
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            custom_fields
            """
        And the returned data "custom_fields" property is an array
        And the returned data "custom_fields" property contains "0" items

    Scenario: Listing of multiple custom fields
        Given I have a "NativeCurlClient" client
        And I create a custom field for issues with the name "Note B"
        And I create a custom field for issues with the name "Note A"
        When I list all custom fields
        Then the response has the status code "200"
        And the response has the content type "application/json"
        And the returned data has only the following properties
            """
            custom_fields
            """
        And the returned data "custom_fields" property is an array
        And the returned data "custom_fields" property contains "2" items
        # field 'description' was added in Redmine 5.1.0, see https://www.redmine.org/issues/37617
        And the returned data "custom_fields.0" property contains the following data with Redmine version ">= 5.1.0"
            | property              | value                |
            | id                    | 1                    |
            | name                  | Note B               |
            | description           | null                 |
            | customized_type       | issue                |
            | field_format          | string               |
            | regexp                |                      |
            | min_length            | null                 |
            | max_length            | null                 |
            | is_required           | false                |
            | is_filter             | false                |
            | searchable            | false                |
            | multiple              | false                |
            | default_value         | null                 |
            | visible               | true                 |
            | trackers              | []                   |
            | roles                 | []                   |
        But the returned data "custom_fields.0" property contains the following data with Redmine version "< 5.1.0"
            | property              | value                |
            | id                    | 1                    |
            | name                  | Note B               |
            | customized_type       | issue                |
            | field_format          | string               |
            | regexp                |                      |
            | min_length            | null                 |
            | max_length            | null                 |
            | is_required           | false                |
            | is_filter             | false                |
            | searchable            | false                |
            | multiple              | false                |
            | default_value         | null                 |
            | visible               | true                 |
            | trackers              | []                   |
            | roles                 | []                   |
        # field 'description' was added in Redmine 5.1.0, see https://www.redmine.org/issues/37617
        And the returned data "custom_fields.1" property contains the following data with Redmine version ">= 5.1.0"
            | property              | value                |
            | id                    | 2                    |
            | name                  | Note A               |
            | description           | null                 |
            | customized_type       | issue                |
            | field_format          | string               |
            | regexp                |                      |
            | min_length            | null                 |
            | max_length            | null                 |
            | is_required           | false                |
            | is_filter             | false                |
            | searchable            | false                |
            | multiple              | false                |
            | default_value         | null                 |
            | visible               | true                 |
            | trackers              | []                   |
            | roles                 | []                   |
        But the returned data "custom_fields.1" property contains the following data with Redmine version "< 5.1.0"
            | property              | value                |
            | id                    | 2                    |
            | name                  | Note A               |
            | customized_type       | issue                |
            | field_format          | string               |
            | regexp                |                      |
            | min_length            | null                 |
            | max_length            | null                 |
            | is_required           | false                |
            | is_filter             | false                |
            | searchable            | false                |
            | multiple              | false                |
            | default_value         | null                 |
            | visible               | true                 |
            | trackers              | []                   |
            | roles                 | []                   |

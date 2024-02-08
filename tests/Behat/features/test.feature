Feature: Behat tests should be executed
    In order to write tests with behat
    As a developer
    I want to make shure behat works as intended

    Scenario: Calling the FeatureContext
        Given an existing FeatureContext
        When I run the tests
        Then some testable outcome is achieved

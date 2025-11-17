@block @block_townsquare @javascript
Feature: In the townsquare block user can save their settings in a database.

  Background:
    Given I prepare a townsquare feature background

  Scenario: Test saving the time filter
    Given I log in as "student1"
    Then I should see "Test assign 2" in the "Town Square" "block"
    And I click on "Time filter" "text"
    And I click on "Next two days" "text"
    Then I should not see "Test assign 2" in the "Town Square" "block"
    When I click on "Save settings" "text" in the "Town Square" "block"
    And I reload the page
    Then I should not see "Test assign 2" in the "Town Square" "block"

  Scenario: Test saving the letter filter
    Given the following "activity" exists:
      | course   | C1              |
      | activity | forum           |
      | name     | Test forum name |
      | idnumber | forum           |
    And the following "mod_forum > discussions" exist:
      | forum | name         | subject      | message                              |
      | forum | Discussion 1 | Discussion 1 | Discussion contents 1, first message |
    And I add a townsquare completion event to "C1"
    When I log in as "student1"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test forum name" in the "Town Square" "block"
    When I click on "Letter filter" "text"
    And I click on "postletter" "checkbox"
    Then I should not see "Test forum name" in the "Town Square" "block"
    When I click on "Save settings" "text" in the "Town Square" "block"
    And I reload the page
    Then I should not see "Test forum name" in the "Town Square" "block"

  Scenario: Test saving the course filter
    Given the following "activity" exists:
      | course   | C1              |
      | activity | forum           |
      | name     | Test forum name |
      | idnumber | forum           |
    And the following "mod_forum > discussions" exist:
      | forum | name         | subject      | message                              |
      | forum | Discussion 1 | Discussion 1 | Discussion contents 1, first message |
    And I add a townsquare completion event to "C1"
    When I log in as "student1"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test forum name" in the "Town Square" "block"
    When I click on "C1" "checkbox"
    Then I should not see "Test forum name" in the "Town Square" "block"
    When I click on "Save settings" "text" in the "Town Square" "block"
    And I reload the page
    Then I should not see "Test forum name" in the "Town Square" "block"
@block @block_townsquare @javascript
Feature: In the townsquare block user can save their settings in a database.

  Background:
    Given I prepare a townsquare feature background

  Scenario: Test saving the time filter
    Given I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    Then I should see "Test assign 2" in the "Town Square" "block"
    And I select the townsquare time span "2 days"
    Then I should not see "Test assign 2" in the "Town Square" "block"
    When I save the townsquare settings
    And I reload the page
    And I click on "Deadlines" "button" in the "Town Square" "block"
    Then I should not see "Test assign 2" in the "Town Square" "block"

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
    Then I should see "Test forum name" in the "Town Square" "block"
    When I open the townsquare course filter
    And I click on "C1" "checkbox"
    Then I should not see "Test forum name" in the "Town Square" "block"
    When I save the townsquare settings
    And I reload the page
    Then I should not see "Test forum name" in the "Town Square" "block"

  Scenario: The selected tab is not saved
    Given I log in as "student1"
    When I click on "Deadlines" "button" in the "Town Square" "block"
    And I save the townsquare settings
    And I reload the page
    Then the "aria-pressed" attribute of "#ts_tab_posts" "css_element" should contain "true"
    And the "aria-pressed" attribute of "#ts_tab_deadlines" "css_element" should contain "false"

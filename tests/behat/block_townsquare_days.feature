@block @block_townsquare @javascript
Feature: The townsquare block groups the letters by day
  In order to see when something happens
  As a student
  I see the letters grouped by day in the townsquare block

  Background:
    Given I prepare a townsquare feature background

  Scenario: Test the day headers
    Given the following "activities" exist:
      | activity | course | idnumber | name          | intro       | timeopen        | duedate         |
      | assign   | C2     | 13       | Test assign 4 | Description | ##now -2 days## | ##now +1 days## |
    And I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    # Test assign 1 (course 1) and Test assign 4 (course 2) are due tomorrow.
    Then I should see "Tomorrow" in the "Town Square" "block"
    And I should see "2" in the "//div[contains(@class, 'ts_day_header')][contains(., 'Tomorrow')]//*[@data-region='ts_day_count']" "xpath_element"
    When I open the townsquare course filter
    And I click on "C2" "checkbox"
    Then I should see "1" in the "//div[contains(@class, 'ts_day_header')][contains(., 'Tomorrow')]//*[@data-region='ts_day_count']" "xpath_element"
    When I click on "C1" "checkbox"
    Then I should not see "Tomorrow" in the "Town Square" "block"

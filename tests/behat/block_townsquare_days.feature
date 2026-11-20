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

  Scenario: Test the order of the letters
    Given the following "activities" exist:
      | activity | course | idnumber | name          | intro       | timeopen        | duedate         |
      | assign   | C1     | 13       | Test assign 4 | Description | ##now -9 days## | ##now -2 days## |
      | assign   | C2     | 14       | Test assign 5 | Description | ##now -9 days## | ##now -4 days## |
    And I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    # Today and the upcoming days come first (next deadline first), then the past days (latest first).
    Then "Test assign 1" "text" should appear before "Test assign 2" "text"
    And "Test assign 2" "text" should appear before "Test assign 3" "text"
    And "Test assign 3" "text" should appear before ".ts_past_heading" "css_element"
    And ".ts_past_heading" "css_element" should appear before "Test assign 4" "text"
    And "Test assign 4" "text" should appear before "Test assign 5" "text"
    And I should see "Past" in the "Town Square" "block"
    # Without past letters, the heading is not shown.
    # Test assign 4 and 5 (the past ones) are in course 1 and 2.
    When I open the townsquare course filter
    And I click in townsquare on "checkbox" type:
      | C1 | C2 |
    Then I should see "Test assign 3" in the "Town Square" "block"
    And I should not see "Past" in the "Town Square" "block"

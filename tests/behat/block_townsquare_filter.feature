@block @block_townsquare @javascript
Feature: The townsquare block allows users to see notifications from different courses
  In order to enable the townsquare block
  As a student
  I can add the townsquare block to my dashboard

  Background:
    Given I prepare a townsquare feature background

  Scenario: Test the course filter
    Given I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 2 | Test assign 3 |
    When I open the townsquare course filter
    And I click on "C1" "checkbox"
    Then I should not see "Test assign 1" in the "Town Square" "block"
    And I should "" see in townsquare the elements:
      | Test assign 2 | Test assign 3 |
    When I click on "C2" "checkbox"
    Then I should not see "Test assign 2" in the "Town Square" "block"
    And  I should see "Test assign 3" in the "Town Square" "block"
    When I click on "C3" "checkbox"
    Then I should not see "Test assign 3" in the "Town Square" "block"

  Scenario: Test the time filter
    Given the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
      | assign   | C1     | 13        | Test assign 4 | Assign due in 8 days   | ##now -2 days##   | ##now +8 days##   |
      | assign   | C1     | 14        | Test assign 5 | Assign due in 3 months | ##now -2 days##   | ##now +2 months## |
      | assign   | C1     | 15        | Test assign 6 | Assign due tomorrow    | ##now -2 days##   | ##now -1 days##   |
      | assign   | C1     | 16        | Test assign 7 | Assign due yesterday   | ##now -2 days##   | ##now -4 days##   |
      | assign   | C1     | 17        | Test assign 8 | Assign due in 2 months | ##now -2 days##   | ##now -6 days##   |
    And the following "activities" exist:
      | activity | course | idnumber  | name            | intro                 | timeopen         | timeclose         |
      | choice   | C2     | 18        | Test choice 1   | Choice description    | ##now -8 days##  | ##now -8 days##   |
      | choice   | C2     | 19        | Test choice 2   | Choice description    | ##now -2 months##| ##now -2 months## |
    And I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    And I change window size to "large"
    # A time span covers the same time before and after now.
    When I select the townsquare time span "2 days"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 6 |
    And I should "not" see in townsquare the elements:
      | Test assign 2 | Test assign 7 |
    When I select the townsquare time span "5 days"
    Then I should "" see in townsquare the elements:
      | Test assign 2 | Test assign 7 |
    And I should "not" see in townsquare the elements:
      | Test assign 3 | Test assign 8 |
    When I select the townsquare time span "1 week"
    Then I should "" see in townsquare the elements:
      | Test assign 3 | Test assign 8 |
    And I should "not" see in townsquare the elements:
      | Test assign 4 | Test choice 1 |
    When I select the townsquare time span "1 month"
    Then I should "" see in townsquare the elements:
      | Test assign 4 | Test choice 1 |
    And I should "not" see in townsquare the elements:
      | Test assign 5 | Test choice 2 |
    When I select the townsquare time span "All time"
    Then I should "" see in townsquare the elements:
      | Test assign 5 | Test choice 2 |

  Scenario: Test the letter tabs
    Given the following "activity" exists:
      | course   | C1              |
      | activity | forum           |
      | name     | Test forum name |
      | idnumber | forum           |
    And the following "mod_forum > discussions" exist:
      | forum | name         | subject      | message                              |
      | forum | Discussion 1 | Discussion 1 | Discussion contents 1, first message |
    And I add a townsquare completion event to "C1"
    And I log in as "student1"
    # The posts tab is selected first.
    Then I should "" see in townsquare the elements:
      | Test forum name |
    And I should "not" see in townsquare the elements:
      | Test assign 1 | Assignment 1 |
    When I click on "Deadlines" "button" in the "Town Square" "block"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Assignment 1 |
    And I should "not" see in townsquare the elements:
      | Test forum name |
    When I click on "Posts" "button" in the "Town Square" "block"
    Then I should "" see in townsquare the elements:
      | Test forum name |
    And I should "not" see in townsquare the elements:
      | Test assign 1 | Assignment 1 |

  Scenario: Test the labels of the filter bar
    Given I log in as "student1"
    Then I should see "All courses" in the "#ts_course_filter_button" "css_element"
    And I should see "All time" in the "#ts_time_filter_button" "css_element"
    When I open the townsquare course filter
    And I click on "C1" "checkbox"
    Then I should see "2 of 3 courses" in the "#ts_course_filter_button" "css_element"
    When I select the townsquare time span "1 week"
    Then I should see "±1 week" in the "#ts_time_filter_button" "css_element"
    When I select the townsquare time span "All time"
    Then I should see "All time" in the "#ts_time_filter_button" "css_element"

  Scenario: An empty tab shows a message
    Given I log in as "student1"
    # The background has no posts.
    Then I should see "No posts to show" in the "Town Square" "block"
    When I click on "Deadlines" "button" in the "Town Square" "block"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should not see "No posts to show" in the "Town Square" "block"
    And I should not see "No deadlines to show" in the "Town Square" "block"
    When I open the townsquare course filter
    And I click in townsquare on "checkbox" type:
      | C1 | C2 | C3 |
    Then I should see "No deadlines to show" in the "Town Square" "block"
    And I should see "The course or time filter may hide some items." in the "Town Square" "block"
    # Every tab has its own message, only the one of the selected tab is visible.
    And I press the escape key
    When I click on "Show all" "button" in the "[data-tab='ts_tab_deadlines']" "css_element"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "All courses" in the "#ts_course_filter_button" "css_element"

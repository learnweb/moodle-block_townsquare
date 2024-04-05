@block @block_townsquare @javascript @core_completion
Feature: The townsquare block allows users to see notifications from different courses
  In order to enable the townsquare block
  As a student
  I can add the townsquare block to my dashboard

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | student1 | Tamaro    | Walter   | student1@example.com | S1       |
    And the following "courses" exist:
      | fullname | shortname | category | startdate     | enddate           | enablecompletion | showcompletionconditions |
      | Course 1 | C1        | 0        | ##yesterday## | ##now +6 months## | 1                | 1                        |
      | Course 2 | C2        | 0        | ##yesterday## | ##now +6 months## | 1                | 1                        |
      | Course 3 | C3        | 0        | ##yesterday## | ##now +6 months## | 1                | 1                        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student1 | C2     | student |
      | student1 | C3     | student |
    And the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate          |
      | assign   | C1     | 10        | Test assign 1 | Assign due in 2 months | ##now -2 days##   | ##now +1 days##  |
      | assign   | C2     | 11        | Test assign 2 | Assign due in 4 days   | ##now -2 days##   | ##now +4 days##  |
      | assign   | C3     | 12        | Test assign 3 | Assign due in 6 days   | ##now -2 days##   | ##now +6 days##  |

  Scenario: Test the course filter
    Given I log in as "student1"
    And I turn editing mode on
    When I add the "Town Square" block
    Then I should see "Test assign 1"
    And I should see "Test assign 2"
    And I should see "Test assign 3"
    When I click on "Course 1" "checkbox"
    Then "Test assign 1" "text" should not be visible
    And "Test assign 2" "text" should be visible
    And "Test assign 3" "text" should be visible
    When I click on "Course 2" "checkbox"
    Then "Test assign 2" "text" should not be visible
    And  "Test assign 3" "text" should be visible
    When I click on "Course 3" "checkbox"
    Then "Test assign 3" "text" should not be visible


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
    And I turn editing mode on
    And I add the "Town Square" block to the "content" region
    And I click on "Time filter" "text"
    # Test the time filter for the future
    When I click on "Next two days" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should not see "Test assign 2" in the "Town Square" "block"
    And I should not see "Choice description" in the "Town Square" "block"
    When I click on "Next five days" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 2" in the "Town Square" "block"
    And I should not see "Test assign 3" in the "Town Square" "block"
    And I should not see "Choice description" in the "Town Square" "block"
    When I click on "Next week" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 2" in the "Town Square" "block"
    And I should see "Test assign 3" in the "Town Square" "block"
    And I should not see "Test assign 4" in the "Town Square" "block"
    And I should not see "Choice description" in the "Town Square" "block"
    When I click on "Next month" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 3" in the "Town Square" "block"
    And I should see "Test assign 4" in the "Town Square" "block"
    And I should not see "Test assign 5" in the "Town Square" "block"
    And I should not see "Choice description" in the "Town Square" "block"
    # All notifications button
    When I click on "All notifications" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 3" in the "Town Square" "block"
    And I should see "Test assign 5" in the "Town Square" "block"
    # Test time filter for the past
    When I click on "Last two days" "text"
    Then I should see "Test assign 6" in the "Town Square" "block"
    And I should not see "Test assign 7" in the "Town Square" "block"
    And I should not see "Test assign 1" in the "Town Square" "block"
    When I click on "Last five days" "text"
    Then I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 7" in the "Town Square" "block"
    And I should not see "Test assign 8" in the "Town Square" "block"
    And I should not see "Test assign 1" in the "Town Square" "block"
    When I click on "Last week" "text"
    Then I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 8" in the "Town Square" "block"
    And I should not see "Test choice 1" in the "Town Square" "block"
    When I click on "Last month" "text"
    Then I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 8" in the "Town Square" "block"
    And I should see "Test choice 1" in the "Town Square" "block"
    And I should not see "Test choice 2" in the "Town Square" "block"
    When I click on "All notifications" "text"
    Then I should see "Test choice 2" in the "Town Square" "block"
    # Test different combinations of time filters
    When I click on "Next two days" "text"
    And I click on "Last five days" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should not see "Test assign 2" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 7" in the "Town Square" "block"
    And I should not see "Test assign 8" in the "Town Square" "block"
    When I click on "Next week" "text"
    And I click on "Last two days" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 3" in the "Town Square" "block"
    And I should not see "Test assign 4" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should not see "Test assign 7" in the "Town Square" "block"
    When I click on "Next month" "text"
    And I click on "Last week" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 4" in the "Town Square" "block"
    And I should not see "Test assign 5" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 8" in the "Town Square" "block"
    And I should not see "Test Choice 1" in the "Town Square" "block"

  Scenario: Test the letter filter
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
    And I turn editing mode on
    And I add the "Town Square" block to the "content" region
    And I click on "Letter filter" "text"
    Then I should see "Test forum name" in the "Town Square" "block"
    And I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Assignment 1" in the "Town Square" "block"
    When I click on "basicletter" "checkbox"
    And I should not see "Test assign 1" in the "Town Square" "block"
    And I should see "Test forum name" in the "Town Square" "block"
    And I should see "Assignment 1" in the "Town Square" "block"
    When I click on "postletter" "checkbox"
    Then I should not see "Test forum name" in the "Town Square" "block"
    And I should see "Assignment 1" in the "Town Square" "block"
    When I click on "completionletter" "checkbox"
    Then I should not see "Assignment 1" in the "Town Square" "block"

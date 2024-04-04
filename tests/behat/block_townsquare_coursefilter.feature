@block @block_townsquare @javascript
Feature: The townsquare block allows users to see notifications from different courses
  In order to enable the townsquare block
  As a student
  I can add the townsquare block to my dashboard

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                | idnumber |
      | student1 | Tamaro    | Walter   | student1@example.com | S1       |
    And the following "courses" exist:
      | fullname | shortname | category | startdate                   | enddate      |
      | Course 1 | C1        | 0        | ##yesterday##               | ##now +6 months## |
      | Course 2 | C2        | 0        | ##yesterday##               | ##now +6 months## |
      | Course 3 | C3        | 0        | ##yesterday##               | ##now +6 months## |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student1 | C2     | student |
      | student1 | C3     | student |
    And the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate          |
      | assign   | C1     | assign1   | Test assign 1 | Assign due in 2 months | ##now -2 days##   | ##now +1 days##  |
      | assign   | C2     | assign2   | Test assign 2 | Assign due in 4 days   | ##now -2 days##   | ##now +4 days##  |
      | assign   | C3     | assign3   | Test assign 3 | Assign due in 6 days   | ##now -2 days##   | ##now +6 days##  |

  Scenario: If the user unselects the a course in the course filter, the assignment from that course should be hidden
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

  Scenario: If the user changes the time filter, the content should adapt to the selected time spans
    Given the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate          |
      | assign   | C1     | assign4   | Test assign 4 | Assign due in 2 months | ##now -2 days##   | ##now +8 days##  |
      | assign   | C1     | assign5   | Test assign 5 | Assign due tomorrow    | ##now -2 days##   | ##now -1 days##  |
      | assign   | C1     | assign6   | Test assign 6 | Assign due yesterday   | ##now -2 days##   | ##now -4 days##  |
      | assign   | C1     | assign7   | Test assign 7 | Assign due in 2 months | ##now -2 days##   | ##now -6 days##  |
    And the following "activities" exist:
      | activity | course | idnumber  | name            | intro                 | timeopen         | timeclose        |
      | choice   | C2     | choice1   | Test choice 1   | Choice description    | ##now -10 days## | ##now -8 days##  |
    And I log in as "student1"
    And I turn editing mode on
    And I add the "Town Square" block to the "content" region
    And I click on "Time filter" "text"
    And I click on "Next two days" "text"
    Then "Test assign 1" "text" should be visible
    And "Test assign 2" "text" should not be visible
    And "Choice description" "text" should not be visible
    And I click on "Next five days" "text"
    Then "Test assign 1" "text" should be visible
    And "Test assign 2" "text" should be visible
    And "Test assign 3" "text" should not be visible
    And I click on "Next week" "text"
    Then "Test assign 1" "text" should be visible
    And "Test assign 2" "text" should be visible
    And "Test assign 3" "text" should be visible
    And "Test assign 4" "text" should not be visible


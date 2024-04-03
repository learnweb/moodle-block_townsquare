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
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate             |
      | assign   | C1     | assign1   | Test assign 1 | Assign due in 2 months | ##now -2 days##   | ##now +2 month##    |
      | assign   | C2     | assign2   | Test assign 2 | Assign due tomorrow    | ##now -2 days##   | ##tomorrow##        |
      | assign   | C3     | assign3   | Test assign 3 | Assign due yesterday   | ##now -2 days##   | ##yesterday##       |


    Scenario: If the user unselects the a course in the course filter, the assignment from that course should be hidden
        Given I log in as "student1"
        And I turn editing mode on
        And I add the "Town Square" block
        Then I should see "Test assign 1"
        And I should see "Test assign 2"
        And I should see "Test assign 3"
        And I click on "Course 1" "checkbox"
        Then "C1" "css_element" should not be visible
        And "C2" "css_element" should be visible
        And "C3" "css_element" should be visible
        And I click on "Course 2" "checkbox"
        Then "C2" "css_element" should not be visible
        And  "C3" "css_element" should be visible
        And I click on "Course 3" "checkbox"
        Then "C3" "css_element" should not be visible

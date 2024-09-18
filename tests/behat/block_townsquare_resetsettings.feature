@block @block_townsquare @javascript
Feature: In the townsquare block user can reset their filter settings

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
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
      | assign   | C1     | 10        | Test assign 1 | Assign due in 2 months | ##now -2 days##   | ##now +1 days##   |
      | assign   | C2     | 11        | Test assign 2 | Assign due in 4 days   | ##now -2 days##   | ##now +4 days##   |
      | assign   | C3     | 12        | Test assign 3 | Assign due in 6 days   | ##now -2 days##   | ##now +6 days##   |
      | assign   | C1     | 13        | Test assign 4 | Assign description     | ##now -2 days##   | ##now +8 days##   |
      | assign   | C1     | 15        | Test assign 6 | Assign description     | ##now -2 days##   | ##now -1 days##   |
      | assign   | C1     | 16        | Test assign 7 | Assign description     | ##now -2 days##   | ##now -4 days##   |
      | assign   | C1     | 17        | Test assign 8 | Assign description     | ##now -2 days##   | ##now -6 days##   |
    And the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | timeclose         |
      | choice   | C2     | 18        | Test choice 1 | Choice description     | ##now -8 days##   | ##now -8 days##   |
      | choice   | C2     | 19        | Test choice 2 | Choice description     | ##now -2 months## | ##now -2 months## |
    And the following "activity" exists:
      | course   | C1              |
      | activity | forum           |
      | name     | Test forum name |
      | idnumber | forum           |
    And the following "mod_forum > discussions" exist:
      | forum | name         | subject      | message                              |
      | forum | Discussion 1 | Discussion 1 | Discussion contents 1, first message |
    And the following "blocks" exist:
      | blockname  | contextlevel | reference | pagetypepattern | defaultregion |
      | townsquare | System       | 1         | my-index        | content       |

  Scenario: Test the Reset button
    Given I add a townsquare completion event to "C1"
    And I log in as "student1"
    And I click on "Course 1" "checkbox"
    And I click on "Course 3" "checkbox"
    And I click on "Time filter" "text"
    And I click on "Next week" "text"
    And I click on "Last five days" "text"
    And I click on "Letter filter" "text"
    And I click on "basicletter" "checkbox"
    And I click on "postletter" "checkbox"
    When I click on "Reset Settings" "button"
    And I click on "Course filter" "text"
    Then "Course 1" "checkbox" should exist
    And "Course 2" "checkbox" should exist
    And "Course 3" "checkbox" should exist
    And the following fields match these values:
      | Course 1 | 1 |
      | Course 2 | 1 |
      | Course 3 | 1 |
    When I click on "Letter filter" "text"
    Then "basicletter" "checkbox" should exist
    And "completionletter" "checkbox" should exist
    And "postletter" "checkbox" should exist
    And the following fields match these values:
      | basicletter      | 1 |
      | completionletter | 1 |
      | postletter       | 1 |
    When I click on "Time filter" "text"
    Then "All notifications" "text" should exist
    And the following fields match these values:
      | All notifications | 1 |



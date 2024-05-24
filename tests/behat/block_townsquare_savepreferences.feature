@block @block_townsquare @javascript
Feature: In the townsquare block user can save their settings in a database.

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
    And the following "blocks" exist:
    | blockname  | contextlevel | reference | pagetypepattern | defaultregion |
    | townsquare | System       | 1         | my-index        | content       |

  Scenario: Test the course filter
    Given I log in as "student1"
    Then I should see "Test assign 2" in the "Town Square" "block"
    And I click on "Time filter" "text"
    And I click on "Next two days" "text"
    And I townsquare debug
    When I click on "Save settings" "text" in the "Town Square" "block"
    Then I should not see "Test assign 2" in the "Town Square" "block"
    And I townsquare debug
    And I reload the page
    Then I should not see "Test assign 2" in the "Town Square" "block"
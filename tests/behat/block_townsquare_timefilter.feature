@block @block_townsquare @javascript
Feature: Extra feature file to test the time filter
  In order to enable the townsquare block
  As a student
  I can add the townsquare block to my dashboard

  Scenario: Test wild time filter combinations
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
    Given the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
      | assign   | C1     | 10        | Test assign 1 | Assign description     | ##now -2 days##   | ##now +1 days##   |
      | assign   | C2     | 11        | Test assign 2 | Assign description     | ##now -2 days##   | ##now +4 days##   |
      | assign   | C3     | 12        | Test assign 3 | Assign description     | ##now -2 days##   | ##now +6 days##   |
      | assign   | C1     | 13        | Test assign 4 | Assign description     | ##now -2 days##   | ##now +8 days##   |
      | assign   | C1     | 14        | Test assign 5 | Assign description     | ##now -2 days##   | ##now +2 months## |
      | assign   | C1     | 15        | Test assign 6 | Assign description     | ##now -2 days##   | ##now -1 days##   |
      | assign   | C1     | 16        | Test assign 7 | Assign description     | ##now -2 days##   | ##now -4 days##   |
      | assign   | C1     | 17        | Test assign 8 | Assign description     | ##now -2 days##   | ##now -6 days##   |
    And the following "activities" exist:
      | activity | course | idnumber  | name            | intro                 | timeopen         | timeclose         |
      | choice   | C2     | 18        | Test choice 1   | Choice description    | ##now -8 days##  | ##now -8 days##   |
      | choice   | C2     | 19        | Test choice 2   | Choice description    | ##now -2 months##| ##now -2 months## |
      | choice   | C1     | 32        | Test choice 3   | Choice description    | ##now -2 weeks## | ##now -2 weeks##  |
      | choice   | C2     | 33        | Test choice 4   | Choice description    | ##now -3 days##  | ##now -3 days##   |
      | choice   | C3     | 34        | Test choice 5   | Choice description    | ##now -6 days##  | ##now -6 days##   |
      | choice   | C1     | 35        | Test choice 6   | Choice description    | ##now -1 months##| ##now -1 months## |
      | choice   | C2     | 36        | Test choice 7   | Choice description    | ##now -4 days##  | ##now -4 days##   |
      | choice   | C3     | 37        | Test choice 8   | Choice description    | ##now -2 days##  | ##now -2 days##   |
    And the following "blocks" exist:
      | blockname  | contextlevel | reference | pagetypepattern | defaultregion |
      | townsquare | System       | 1         | my-index        | content       |
    And I log in as "student1"
    And I click on "Time filter" "text"
    # Random clicks on different time filters
    When I click on "Last week" "text"
    And I click on "Next month" "text"
    And I click on "Last two days" "text"
    And I click on "Next week" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should not see "Test assign 4" in the "Town Square" "block"
    And I should not see "Test assign 5" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should not see "Test assign 8" in the "Town Square" "block"
    And I should not see "Test choice 1" in the "Town Square" "block"
    And I should not see "Test choice 2" in the "Town Square" "block"
    When I click on "Next two days" "text"
    And I click on "Last month" "text"
    And I click on "Last five days" "text"
    And I click on "Next month" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 3" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should not see "Test assign 8" in the "Town Square" "block"
    And I should not see "Test assign 5" in the "Town Square" "block"
    And I should not see "Test choice 1" in the "Town Square" "block"
    And I should see "Test choice 7" in the "Town Square" "block"
    When I click on "Next five days" "text"
    And I click on "Last week" "text"
    And I click on "All notifications" "text"
    Then I should see "Test assign 4" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test choice 3" in the "Town Square" "block"
    And I should see "Test choice 5" in the "Town Square" "block"
    And I should see "Test assign 7" in the "Town Square" "block"
    And I should see "Test choice 4" in the "Town Square" "block"
    When I click on "Last month" "text"
    And I click on "Next week" "text"
    And I click on "Next month" "text"
    And I click on "Last two days" "text"
    Then I should see "Test assign 6" in the "Town Square" "block"
    And I should not see "Test assign 8" in the "Town Square" "block"
    And I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 4" in the "Town Square" "block"
    And I should not see "Test assign 5" in the "Town Square" "block"
    And I should not see "Test choice 7" in the "Town Square" "block"
    When I click on "Next week" "text"
    And I click on "Last week" "text"
    And I click on "Last five days" "text"
    Then I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 7" in the "Town Square" "block"
    And I should not see "Test assign 4" in the "Town Square" "block"
    And I should see "Test assign 1" in the "Town Square" "block"
    And I should not see "Test assign 8" in the "Town Square" "block"
    When I click on "Next five days" "text"
    And I click on "All notifications" "text"
    Then I should see "Test assign 1" in the "Town Square" "block"
    And I should see "Test assign 3" in the "Town Square" "block"
    And I should see "Test assign 6" in the "Town Square" "block"
    And I should see "Test assign 7" in the "Town Square" "block"
    And I should see "Test assign 4" in the "Town Square" "block"
    And I should see "Test choice 1" in the "Town Square" "block"
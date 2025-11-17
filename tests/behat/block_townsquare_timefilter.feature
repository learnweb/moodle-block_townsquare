@block @block_townsquare @javascript
Feature: Extra feature file to test the time filter
  In order to enable the townsquare block
  As a student
  I can add the townsquare block to my dashboard

  Scenario: Test wild time filter combinations
    Given I prepare a townsquare feature background
    Given the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
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
    And I log in as "student1"
    # Random clicks on different time filters
    And I click in townsquare on "text" type:
      | Time filter | Last week | Next month | Last two days | Next week |
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 6 |
    And I should "not" see in townsquare the elements:
      | Test assign 4 | Test assign 5 | Test assign 8 | Test choice 1 | Test choice 2 |
    When I click in townsquare on "text" type:
      | Next two days | Last month | Last five days | Next month |
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 3 | Test assign 6 | Test assign 7 |
    And I should "not" see in townsquare the elements:
      | Test choice 1 | Test assign 5 | Test assign 8 |
    When I click in townsquare on "text" type:
      | Next five days | Last week | All notifications |
    Then I should "" see in townsquare the elements:
      | Test assign 4 | Test assign 6 | Test choice 3 | Test choice 4 | Test choice 5 | Test assign 7 |
    When I click in townsquare on "text" type:
      | Last month | Next week | Next month | Last two days |
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 4 | Test assign 6 |
    And I should "not" see in townsquare the elements:
      | Test assign 5 | Test assign 8 | Test choice 7 |
    When I click in townsquare on "text" type:
      | Next week | Last week | Last five days |
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 6 | Test assign 7 |
    And I should "not" see in townsquare the elements:
      | Test assign 4 | Test assign 8 |
    When I click in townsquare on "text" type:
      | Next five days | All notifications |
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 3 | Test assign 4 | Test assign 6 | Test assign 7 | Test choice 1 |

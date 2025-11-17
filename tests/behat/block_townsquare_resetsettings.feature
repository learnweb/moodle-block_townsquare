@block @block_townsquare @javascript
Feature: In the townsquare block user can reset their filter settings

  Background:
    Given I prepare a townsquare feature background
    And the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
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

  Scenario: Test the Reset button
    Given I add a townsquare completion event to "C1"
    And I log in as "student1"
    And I click in townsquare on "checkbox" type:
      | C1 | C3 |
    And I click in townsquare on "text" type:
      | Time filter | Next week | Last five days | Letter filter |
    And I click in townsquare on "checkbox" type:
      | basicletter | postletter |
    When I click on "Reset Settings" "button"
    And I click on "Course filter" "text"
    Then "C1" "checkbox" should exist
    And "C2" "checkbox" should exist
    And "C3" "checkbox" should exist
    And the following fields match these values:
      | C1 | 1 |
      | C2 | 1 |
      | C3 | 1 |
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

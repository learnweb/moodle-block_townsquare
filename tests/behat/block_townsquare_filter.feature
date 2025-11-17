@block @block_townsquare @javascript
Feature: The townsquare block allows users to see notifications from different courses
  In order to enable the townsquare block
  As a student
  I can add the townsquare block to my dashboard

  Background:
    Given I prepare a townsquare feature background

  Scenario: Test the course filter
    Given I log in as "student1"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 2 | Test assign 3 |
    When I click on "C1" "checkbox"
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
    And I change window size to "large"
    And I click on "Time filter" "text"
    # Test the time filter for the future
    When I click on "Next two days" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 |
    And I should "not" see in townsquare the elements:
      | Test assign 2 | Choice description |
    When I click on "Next five days" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 2 |
    And I should "not" see in townsquare the elements:
      | Test assign 3 | Choice description |
    When I click on "Next week" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 2 | Test assign 3 |
    And I should "not" see in townsquare the elements:
      | Test assign 4 | Choice description |
    When I click on "Next month" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 3 | Test assign 4 |
    And I should "not" see in townsquare the elements:
      | Test assign 5 | Choice description |
    # All notifications button
    When I click on "All notifications" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 3 | Test assign 5 |
    # Test time filter for the past
    When I click on "Last two days" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 6 |
    And I should "not" see in townsquare the elements:
      | Test assign 1 | Test assign 7 |
    When I click on "Last five days" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 6 | Test assign 7 |
    And I should "not" see in townsquare the elements:
      | Test assign 1 | Test assign 8 |
    When I click on "Last week" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 6 | Test assign 8 |
    And I should "not" see in townsquare the elements:
      | Test choice 1 |
    When I click on "Last month" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 6 | Test assign 8 | Test choice 1 |
    And I should "not" see in townsquare the elements:
      | Test choice 2 |
    When I click on "All notifications" "text"
    Then I should "" see in townsquare the elements:
      | Test choice 2 |
    # Test different combinations of time filters
    When I click on "Next two days" "text"
    And I click on "Last five days" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 6 | Test assign 7 |
    And I should "not" see in townsquare the elements:
      | Test assign 2 | Test assign 8 |
    When I click on "Next week" "text"
    And I click on "Last two days" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 3 | Test assign 6 |
    And I should "not" see in townsquare the elements:
      | Test assign 4 | Test assign 7 |
    When I click on "Next month" "text"
    And I click on "Last week" "text"
    Then I should "" see in townsquare the elements:
      | Test assign 1 | Test assign 4 | Test assign 6 | Test assign 8 |
    And I should "not" see in townsquare the elements:
      | Test assign 5 | Test choice 1 |

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
    And I click on "Letter filter" "text"
    Then I should "" see in townsquare the elements:
      | Test forum name | Test assign 1 | Assignment 1 |
    When I click on "basicletter" "checkbox"
    Then I should "" see in townsquare the elements:
      | Test forum name | Assignment 1 |
    And I should "not" see in townsquare the elements:
      | Test assign 1 |
    When I click on "postletter" "checkbox"
    Then I should "" see in townsquare the elements:
      | Assignment 1 |
    And I should "not" see in townsquare the elements:
      | Test forum name |
    When I click on "completionletter" "checkbox"
    And I should "not" see in townsquare the elements:
      | Assignment 1 |

@block @block_townsquare @javascript
Feature: The townsquare block updates automatically every 30 min. Users can reload the content

  Background:
    Given I prepare a townsquare feature background

  Scenario: A new events gets added that I do not see until i reload townsquare.
    Given I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    And the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
      | assign   | C1     | 13        | Test assign 4 | Assign due in 8 days   | ##now -2 days##   | ##now +8 days##   |
    When I reload the page
    And I click on "Deadlines" "button" in the "Town Square" "block"
    Then I should "not" see in townsquare the elements:
      | Test assign 4 |
    When I reload the townsquare block
    Then I should "" see in townsquare the elements:
      | Test assign 4 |

  Scenario: The filters are kept after a reload
    Given I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    When I open the townsquare course filter
    And I click on "C1" "checkbox"
    Then I should not see "Test assign 1" in the "Town Square" "block"
    And the following "activities" exist:
      | activity | course | idnumber | name          | intro       | timeopen        | duedate         |
      | assign   | C1     | 13       | Test assign 4 | Description | ##now -2 days## | ##now +8 days## |
      | assign   | C2     | 14       | Test assign 5 | Description | ##now -2 days## | ##now +8 days## |
    When I reload the townsquare block
    # The new assignment of course 2 is shown after the reload, the assignments of course 1 stay hidden.
    Then I should see "Test assign 5" in the "Town Square" "block"
    And I should "not" see in townsquare the elements:
      | Test assign 1 | Test assign 4 |

  Scenario: A course that shows letters for the first time after a reload is added to the course filter
    Given the following "courses" exist:
      | fullname | shortname | category | startdate      | enddate        | enablecompletion |
      | Course 4 | C4        | 0        | ##-1 month##   | ##+1 year##    | 1                |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C4     | student |
    And I log in as "student1"
    And I click on "Deadlines" "button" in the "Town Square" "block"
    And the following "activities" exist:
      | activity | course | idnumber | name          | intro       | timeopen        | duedate         |
      | assign   | C4     | 13       | Test assign 4 | Description | ##now -2 days## | ##now +8 days## |
    When I reload the townsquare block
    Then I should see "Test assign 4" in the "Town Square" "block"
    When I open the townsquare course filter
    And I click on "C4" "checkbox"
    Then I should not see "Test assign 4" in the "Town Square" "block"
    And I should see "3 of 4 courses" in the "Town Square" "block"

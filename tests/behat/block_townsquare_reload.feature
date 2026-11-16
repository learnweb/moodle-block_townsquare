@block @block_townsquare @javascript
Feature: The townsquare block updates automatically every 30 min. Users can reload the content

  Background:
    Given I prepare a townsquare feature background

  Scenario: A new events gets added that I do not see until i reload townsquare.
    Given I log in as "student1"
    And the following "activities" exist:
      | activity | course | idnumber  | name          | intro                  | timeopen          | duedate           |
      | assign   | C1     | 13        | Test assign 4 | Assign due in 8 days   | ##now -2 days##   | ##now +8 days##   |
    When I reload the page
    Then I should "not" see in townsquare the elements:
      | Test assign 4 |
    When I reload the townsquare block
    Then I should "" see in townsquare the elements:
      | Test assign 4 |

  Scenario: The filters are kept after a reload
    Given I log in as "student1"
    When I click on "C1" "checkbox"
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

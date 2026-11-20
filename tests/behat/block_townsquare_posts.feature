@block @block_townsquare @javascript
Feature: The townsquare block shows the posts of forums
  In order to read the posts of my courses
  As a student
  I can expand long posts in the townsquare block

  Background:
    Given I prepare a townsquare feature background
    And the following "activity" exists:
      | course   | C1              |
      | activity | forum           |
      | name     | Test forum name |
      | idnumber | forum           |

  Scenario: Long posts can be expanded
    Given the following "mod_forum > discussions" exist:
      | forum | name             | subject          | message         |
      | forum | Short discussion | Short discussion | A short message |
      | forum | Long discussion  | Long discussion  | The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. The lecture on Thursday moves to room 204, please bring your notes from last week. |
    And I log in as "student1"
    And I change window size to "large"
    Then I should not see "Show more" in the "//div[contains(concat(' ', @class, ' '), ' townsquare_letter ')][contains(., 'Short discussion')]" "xpath_element"
    And I should see "Show more" in the "//div[contains(concat(' ', @class, ' '), ' townsquare_letter ')][contains(., 'Long discussion')]" "xpath_element"
    When I click on "Show more" "button" in the "//div[contains(concat(' ', @class, ' '), ' townsquare_letter ')][contains(., 'Long discussion')]" "xpath_element"
    Then I should see "Show less" in the "//div[contains(concat(' ', @class, ' '), ' townsquare_letter ')][contains(., 'Long discussion')]" "xpath_element"
    When I click on "Show less" "button" in the "//div[contains(concat(' ', @class, ' '), ' townsquare_letter ')][contains(., 'Long discussion')]" "xpath_element"
    Then I should see "Show more" in the "//div[contains(concat(' ', @class, ' '), ' townsquare_letter ')][contains(., 'Long discussion')]" "xpath_element"

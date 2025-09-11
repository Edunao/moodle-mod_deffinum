@mod @mod_deffinum
Feature: Deffinum availability
  In order to control when a DEFFINUM activity is available to students
  As a teacher
  I need be able to set availability dates for the DEFFINUM

  Background:
    Given the following "users" exist:
      | username | firstname  | lastname  | email                |
      | student1 | Student    | 1         | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user      | course | role    |
      | student1  | C1     | student |
    And the following "activities" exist:
      | activity | course | name          | packagefilepath                                | timeopen      | timeclose     |
      | deffinum    | C1     | Past DEFFINUM    | mod/deffinum/tests/packages/singlesco_deffinum12.zip | ##yesterday## | ##yesterday## |
      | deffinum    | C1     | Current DEFFINUM | mod/deffinum/tests/packages/singlesco_deffinum12.zip | ##yesterday## | ##tomorrow##  |
      | deffinum    | C1     | Future DEFFINUM  | mod/deffinum/tests/packages/singlesco_deffinum12.zip | ##tomorrow##  | ##tomorrow##  |

  Scenario: Deffinum activity with dates in the past should not be available.
    When I am on the "Past DEFFINUM" "deffinum activity" page logged in as "student1"
    Then the activity date in "Past DEFFINUM" should contain "Opened:"
    And the activity date in "Past DEFFINUM" should contain "##yesterday noon##%A, %d %B %Y, %I:%M##"
    And the activity date in "Past DEFFINUM" should contain "Closed:"
    And the activity date in "Past DEFFINUM" should contain "##yesterday noon##%A, %d %B %Y, %I:%M##"
    And "Enter" "button" should not exist
    And I should not see "Preview"
    And I am on the "Current DEFFINUM" "deffinum activity" page
    And the activity date in "Current DEFFINUM" should contain "Opened:"
    And the activity date in "Current DEFFINUM" should contain "##yesterday noon##%A, %d %B %Y, %I:%M##"
    And the activity date in "Current DEFFINUM" should contain "Closes:"
    And the activity date in "Current DEFFINUM" should contain "##tomorrow noon##%A, %d %B %Y, %I:%M##"
    And "Enter" "button" should exist
    And I should see "Preview"
    And I am on the "Future DEFFINUM" "deffinum activity" page
    And the activity date in "Future DEFFINUM" should contain "Opens:"
    And the activity date in "Future DEFFINUM" should contain "##tomorrow noon##%A, %d %B %Y, %I:%M##"
    And the activity date in "Future DEFFINUM" should contain "Closes:"
    And the activity date in "Future DEFFINUM" should contain "##tomorrow noon##%A, %d %B %Y, %I:%M##"
    And "Enter" "button" should not exist
    And I should not see "Preview"

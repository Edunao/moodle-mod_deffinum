@mod @mod_deffinum @_file_upload @_switch_iframe
Feature: Add deffinum activity
  In order to let students access a deffinum package
  As a teacher
  I need to add deffinum activity to a course

  @javascript
  Scenario: Add a deffinum activity to a course
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | teacher1 | Teacher | 1 | teacher1@example.com |
      | student1 | Student | 1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    When I log in as "teacher1"
    And I add a deffinum activity to course "Course 1" section "1"
    And I set the following fields to these values:
      | Name | Awesome DEFFINUM package |
      | Description | Description |
    And I upload "mod/deffinum/tests/packages/singlesco_deffinum12.zip" file to "Package file" filemanager
    And I click on "Save and display" "button"
    Then I should see "Awesome DEFFINUM package"
    And I should see "Enter"
    And I should see "Preview"
    And I log out
    And I am on the "Awesome DEFFINUM package" "deffinum activity" page logged in as student1
    And I should see "Enter"
    And I press "Enter"
    And I switch to "deffinum_object" iframe
    And I should see "Not implemented yet"
    And I switch to the main frame
    And I am on "Course 1" course homepage

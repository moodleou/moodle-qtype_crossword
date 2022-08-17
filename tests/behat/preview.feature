@qtype @qtype_crossword
Feature: Preview a Crossword question
  As a teacher
  In order to check my Crossword questions will work for students
  I need to preview them

  Background:
    Given the following "users" exist:
      | username |
      | teacher  |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name          | template |
      | Test questions   | crossword | crossword-001 | normal   |

  @javascript @_switch_window
  Scenario: Preview a Crossword question and submit a correct response.
    When I am on the "crossword-001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I set the field "Word 1" to "BRAZIL"
    And I set the field "Word 2" to "PARIS"
    And I set the field "Word 3" to "ITALY"
    And I press "Submit and finish"
    Then I should see "Correct feedback"
    And I should see "Answer 1: BRAZIL, Answer 2: PARIS, Answer 3: ITALY"

  @javascript @_switch_window
  Scenario: Preview a Crossword question and submit an partially correct response.
    When I am on the "crossword-001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I set the field "Word 1" to "BRAZIL"
    And I set the field "Word 2" to "PARIS"
    And I set the field "Word 3" to "NANNO"
    And I press "Submit and finish"
    Then I should see "Partially correct feedback."
    And I should see "Answer 1: BRAZIL, Answer 2: PARIS, Answer 3: ITALY"

  @javascript @_switch_window
  Scenario: Preview a Crossword question and submit an incorrect response.
    When I am on the "crossword-001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I set the field "Word 1" to "LONDON"
    And I set the field "Word 2" to "HANOI"
    And I set the field "Word 3" to "NANNO"
    And I press "Submit and finish"
    Then I should see "Incorrect feedback."
    And I should see "Answer 1: BRAZIL, Answer 2: PARIS, Answer 3: ITALY"

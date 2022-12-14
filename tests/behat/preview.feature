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
      | questioncategory | qtype     | name          | template                 |
      | Test questions   | crossword | crossword-001 | normal                   |
      | Test questions   | crossword | crossword-002 | unicode                  |
      | Test questions   | crossword | crossword-003 | different_codepoint      |
      | Test questions   | crossword | crossword-004 | sampleimage              |
      | Test questions   | crossword | crossword-005 | clear_incorrect_response |
      | Test questions   | crossword | crossword-006 | normal_with_space        |

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
  Scenario: Preview a Crossword question with sample image.
    When I am on the "crossword-004" "core_question > preview" page logged in as teacher
    Then "//img[@src='@@PLUGINFILE@@/test.jpg']" "xpath_element" should not exist
    And "//img[contains(@src,'question/questiontext')]" "xpath_element" should exist

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

  @javascript @_switch_window
  Scenario: Deleting characters from input clue area.
    When I am on the "crossword-001" "core_question > preview" page logged in as teacher
    And I set the field "Word 1" to "BRAZIL"
    And I set the field "Word 2" to "PARIS"
    And I set the field "Word 3" to "ITALY"
    And I select "2" characters from position "1" in the "Word 1"
    And I press the delete key
    And I select "3" characters from position "3" in the "Word 3"
    And I press the delete key
    Then the field "Word 1" matches value "__AZIL"
    And the field "Word 2" matches value "PARIS"
    And the field "Word 3" matches value "IT___"

  @javascript @_switch_window
  Scenario: Deleting intersect characters from input clue area.
    When I am on the "crossword-001" "core_question > preview" page logged in as teacher
    And I set the field "Word 1" to "BRAZIL"
    And I set the field "Word 2" to "PARIS"
    And I set the field "Word 3" to "ITALY"
    And I select "3" characters from position "2" in the "Word 2"
    And I press the delete key
    Then the field "Word 1" matches value "BR_ZIL"
    And the field "Word 2" matches value "P___S"
    And the field "Word 3" matches value "_TALY"

  @javascript @_switch_window
  Scenario: Preview a Crossword question with unicode UTF-8 correct answer.
    When I am on the "crossword-002" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I enter unicode character "?????????" in the crossword clue "Word 1"
    And I enter unicode character "????????????" in the crossword clue "Word 2"
    And I enter unicode character "?????????" in the crossword clue "Word 3"
    And I press "Submit and finish"
    Then I should see "Correct feedback"
    And I should see "Answer 1: ?????????, Answer 2: ????????????, Answer 3: ?????????"

  @javascript @_switch_window
  Scenario: Preview a Crossword question with unicode UTF-8 answer and submit a partially correct response.
    When I am on the "crossword-002" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I enter unicode character "?????????" in the crossword clue "Word 1"
    And I enter unicode character "?????????" in the crossword clue "Word 2"
    And I enter unicode character "?????????" in the crossword clue "Word 3"
    And I press "Submit and finish"
    Then I should see "Partially correct feedback."
    And I should see "Answer 1: ?????????, Answer 2: ????????????, Answer 3: ?????????"

  @javascript @_switch_window
  Scenario: Preview a Crossword question with unicode UTF-8 answer and submit an incorrect response.
    When I am on the "crossword-002" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I enter unicode character "?????????" in the crossword clue "Word 1"
    And I enter unicode character "?????????" in the crossword clue "Word 2"
    And I enter unicode character "?????????" in the crossword clue "Word 3"
    And I press "Submit and finish"
    Then I should see "Incorrect feedback."
    And I should see "Answer 1: ?????????, Answer 2: ????????????, Answer 3: ?????????"

  @javascript @_switch_window
  Scenario: Preview a Crossword question has two same answers but different code point and submit a correct response.
    When I am on the "crossword-003" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I enter unicode character "Am??lie" in the crossword clue "Word 1"
    And I enter unicode character "Ame??lie" in the crossword clue "Word 2"
    And I press "Submit and finish"
    Then I should see "Correct feedback"
    And I should see "Answer 1: AM??LIE, Answer 2: AM??LIE"

  @javascript @_switch_window
  Scenario: Preview a Crossword question has two same answers but different code point and submit a partially correct response.
    When I am on the "crossword-003" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I enter unicode character "Am??lie" in the crossword clue "Word 1"
    And I enter unicode character "Am??liz" in the crossword clue "Word 2"
    And I press "Submit and finish"
    Then I should see "Partially correct feedback."
    And I should see "Answer 1: AM??LIE, Answer 2: AM??LIE"

  @javascript @_switch_window
  Scenario: Preview a Crossword question has two same answers but different code point and submit an incorrect response.
    When I am on the "crossword-003" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I enter unicode character "Amelie" in the crossword clue "Word 1"
    And I enter unicode character "Amelie" in the crossword clue "Word 2"
    And I press "Submit and finish"
    Then I should see "Incorrect feedback."
    And I should see "Answer 1: AM??LIE, Answer 2: AM??LIE"

  @javascript @_switch_window
  Scenario: Preview a Crossword question with clear incorrect responses option.
    When I am on the "crossword-005" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Interactive with multiple tries"
    And I press "Start again with these options"
    And I set the field "Word 1" to "BRAZIL"
    And I set the field "Word 2" to "PARIT"
    And I set the field "Word 3" to "ITALY"
    And I press "Check"
    And I press "Try again"
    Then the field "Word 1" matches value "BRAZIL"
    And the field "Word 2" matches value "_A_I_"
    And the field "Word 3" matches value "ITALY"

  @javascript @_switch_window
  Scenario: User can type their answer with a space at the beginning.
    When I am on the "crossword-001" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Interactive with multiple tries"
    And I press "Start again with these options"
    And I set the field "Word 1" to "BRAZIL"
    And I set the field "Word 2" to "  RIS"
    And I set the field "Word 3" to "ITALY"
    And I press "Submit and finish"
    Then I should see "Partially correct feedback."
    And the field "Word 1" matches value "BR_ZIL"
    And the field "Word 2" matches value "__RIS"
    And the field "Word 3" matches value "ITALY"

  @javascript @_switch_window
  Scenario: When the user enters a space, the system will replace it with an underscore.
    When I am on the "crossword-006" "core_question > preview" page logged in as teacher
    And I expand all fieldsets
    And I set the field "How questions behave" to "Interactive with multiple tries"
    And I press "Start again with these options"
    And I set the field "Word 1" to "SANTA CLAUS"
    And I set the field "Word 2" to "DECEMBER 25"
    And I set the field "Word 3" to "GRINCH"
    Then the field "Word 1" matches value "SANTA_CLAUS"
    And the field "Word 2" matches value "DECEMBER_25"
    And the field "Word 3" matches value "GRINCH"

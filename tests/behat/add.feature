@qtype @qtype_crossword
Feature: Test creating a Crossword question
  As a teacher
  In order to test my students
  I need to be able to create a Crossword question

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

  Scenario: Create a Crossword question with correct answer.
    When I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "Crossword" question filling the form with:
      | Question name                      | crossword-001               |
      | Question text                      | Crossword question text     |
      | Number of rows                     | 3                           |
      | Number of columns                  | 3                           |
      | id_clue_0                          | Clue 1                      |
      | id_clue_1                          | Clue 2                      |
      | id_clue_2                          | Clue 3                      |
      | id_answer_0                        | AAA                         |
      | id_answer_1                        | BBB                         |
      | id_answer_2                        | CCC                         |
      | id_startrow_0                      | 1                           |
      | id_startrow_1                      | 2                           |
      | id_startrow_2                      | 3                           |
      | id_startcolumn_0                   | 0                           |
      | id_startcolumn_1                   | 0                           |
      | id_startcolumn_2                   | 0                           |
      | For any correct response           | Correct feedback            |
      | For any partially correct response | Partially correct feedback. |
      | For any incorrect response         | Incorrect feedback.         |
    Then I should see "crossword-001"

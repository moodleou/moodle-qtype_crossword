@qtype @qtype_crossword
Feature: Test duplicating a quiz containing a Crossword question
  As a teacher
  In order re-use my courses containing Crossword questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype     | name          | template |
      | Test questions   | crossword | crossword-001 | normal   |
    And the following "activities" exist:
      | activity | name      | course | idnumber |
      | quiz     | Test quiz | C1     | quiz1    |
    And quiz "Test quiz" contains the following questions:
      | crossword-001 | 1 |

  @javascript
  Scenario: Backup and restore a course containing a Crossword question
    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 2 |
      | Schema | Course short name | C2       |
    And I am on the "Course 2" "core_question > course question bank" page
    And I choose "Edit question" action for "crossword-001" in the question bank
    Then the following fields match these values:
      | Question name                      | crossword-001                                       |
      | Question text                      | Crossword question text                             |
      | Number of rows                     | 8                                                   |
      | Number of columns                  | 10                                                  |
      | id_clue_0                          | where is the Christ the Redeemer statue located in? |
      | id_clue_1                          | Eiffel Tower is located in?                         |
      | id_clue_2                          | Where is the Leaning Tower of Pisa?                 |
      | id_answer_0                        | BRAZIL                                              |
      | id_answer_1                        | PARIS                                               |
      | id_answer_2                        | ITALY                                               |
      | id_rowindex_0                      | 1                                                   |
      | id_rowindex_1                      | 0                                                   |
      | id_rowindex_2                      | 3                                                   |
      | id_columnindex_0                   | 0                                                   |
      | id_columnindex_1                   | 2                                                   |
      | id_columnindex_2                   | 2                                                   |
      | For any correct response           | Correct feedback                                    |
      | For any partially correct response | Partially correct feedback.                         |
      | For any incorrect response         | Incorrect feedback.                                 |

@qtype @qtype_crossword
Feature: Test duplicating a quiz containing a Crossword question
  As a teacher
  In order re-use my courses containing Crossword questions
  I need to be able to backup and restore them

  Background:
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
      | Course 2 | C2        | 0        |
      | Course 3 | C3        | 0        |
    And the following "question categories" exist:
      | contextlevel | reference | name             |
      | Course       | C1        | Test questions   |
      | Course       | C2        | Test questions 2 |
      | Course       | C3        | Test questions 3 |
    And the following "questions" exist:
      | questioncategory | qtype     | name          | template                                    |
      | Test questions   | crossword | crossword-001 | normal                                      |
      | Test questions 2 | crossword | crossword-002 | accept_wrong_accents_but_subtract_point     |
      | Test questions 3 | crossword | crossword-003 | accept_wrong_accents_but_not_subtract_point |
    And the following "activities" exist:
      | activity | name        | course | idnumber |
      | quiz     | Test quiz   | C1     | quiz1    |
      | quiz     | Test quiz 2 | C2     | quiz2    |
      | quiz     | Test quiz 3 | C3     | quiz3    |
    And quiz "Test quiz" contains the following questions:
      | crossword-001 | 1 |
    And quiz "Test quiz 2" contains the following questions:
      | crossword-002 | 1 |
    And quiz "Test quiz 3" contains the following questions:
      | crossword-003 | 1 |

  @javascript
  Scenario: Backup and restore a course containing a Crossword question
    When I am on the "Course 1" course page logged in as admin
    And I backup "Course 1" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 4 |
      | Schema | Course short name | C4       |
    And I am on the "Course 4" "core_question > course question bank" page
    And I choose "Edit question" action for "crossword-001" in the question bank
    Then the following fields match these values:
      | Question name                      | crossword-001                                                 |
      | Question text                      | Crossword question text                                       |
      | Number of rows                     | 8                                                             |
      | Number of columns                  | 10                                                            |
      | Accented letters                   | Accented letters must completely match or the answer is wrong |
      | id_clue_0                          | where is the Christ the Redeemer statue located in?           |
      | id_clue_1                          | Eiffel Tower is located in?                                   |
      | id_clue_2                          | Where is the Leaning Tower of Pisa?                           |
      | id_answer_0                        | BRAZIL                                                        |
      | id_answer_1                        | PARIS                                                         |
      | id_answer_2                        | ITALY                                                         |
      | id_startrow_0                      | 1                                                             |
      | id_startrow_1                      | 0                                                             |
      | id_startrow_2                      | 3                                                             |
      | id_startcolumn_0                   | 0                                                             |
      | id_startcolumn_1                   | 2                                                             |
      | id_startcolumn_2                   | 2                                                             |
      | For any correct response           | Correct feedback                                              |
      | For any partially correct response | Partially correct feedback.                                   |
      | For any incorrect response         | Incorrect feedback.                                           |

  @javascript
  Scenario: Backup and restore a course containing Crossword with answer setting accept wrong accents but subtracts points.
    When I am on the "Course 2" course page logged in as admin
    And I backup "Course 2" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 5 |
      | Schema | Course short name | C5       |
    And I am on the "Course 5" "core_question > course question bank" page
    And I choose "Edit question" action for "crossword-002" in the question bank
    Then the following fields match these values:
      | Question name                            | crossword-002                                                             |
      | Question text                            | Crossword question text                                                   |
      | Number of rows                           | 12                                                                        |
      | Number of columns                        | 7                                                                         |
      | Accented letters                         | Partial mark if the letters are correct but one or more accents are wrong |
      | Grade for answers with incorrect accents | 25%                                                                       |
      | id_clue_0                                | Des accompagnements à base de foie animal ?                               |
      | id_clue_1                                | Appareil utilisé pour passer des appels ?                                 |
      | id_answer_0                              | PÂTÉ                                                                      |
      | id_answer_1                              | TÉLÉPHONE                                                                 |
      | id_startrow_0                            | 0                                                                         |
      | id_startrow_1                            | 0                                                                         |
      | id_startcolumn_0                         | 0                                                                         |
      | id_startcolumn_1                         | 2                                                                         |
      | id_orientation_0                         | 0                                                                         |
      | id_orientation_1                         | 1                                                                         |
      | For any correct response                 | Correct feedback                                                          |
      | For any partially correct response       | Partially correct feedback.                                               |
      | For any incorrect response               | Incorrect feedback.                                                       |

  @javascript
  Scenario: Backup and restore a course containing Crossword with answer setting accept wrong accents but not subtracts points.
    When I am on the "Course 3" course page logged in as admin
    And I backup "Course 3" course using this options:
      | Confirmation | Filename | test_backup.mbz |
    And I restore "test_backup.mbz" backup into a new course using this options:
      | Schema | Course name       | Course 6 |
      | Schema | Course short name | C6       |
    And I am on the "Course 6" "core_question > course question bank" page
    And I choose "Edit question" action for "crossword-003" in the question bank
    Then the following fields match these values:
      | Question name                      | crossword-003                                 |
      | Question text                      | Crossword question text                       |
      | Number of rows                     | 12                                            |
      | Number of columns                  | 7                                             |
      | Accented letters                   | Just grade the letters and ignore any accents |
      | id_clue_0                          | Des accompagnements à base de foie animal ?   |
      | id_clue_1                          | Appareil utilisé pour passer des appels ?     |
      | id_answer_0                        | PÂTÉ                                          |
      | id_answer_1                        | TÉLÉPHONE                                     |
      | id_startrow_0                      | 0                                             |
      | id_startrow_1                      | 0                                             |
      | id_startcolumn_0                   | 0                                             |
      | id_startcolumn_1                   | 2                                             |
      | id_orientation_0                   | 0                                             |
      | id_orientation_1                   | 1                                             |
      | For any correct response           | Correct feedback                              |
      | For any partially correct response | Partially correct feedback.                   |
      | For any incorrect response         | Incorrect feedback.                           |

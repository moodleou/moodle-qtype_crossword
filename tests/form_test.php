<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the crossword question editing form.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license  https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_crossword;
use qtype_crossword_test_helper;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/type/crossword/tests/helper.php');

/**
 * Unit tests for qtype_crossword editing form.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class form_test extends \advanced_testcase {

    /**
     * Data provider for test_form_validation() test cases.
     *
     * @coversNothing
     * @return array List of data sets (test cases)
     */
    public function test_form_validation_provider(): array {
        return [
            'Normal case' => [
                [
                    'noanswers' => 3,
                    'answer' => [
                        'BRAZIL', 'PARIS', 'ITALY'
                    ],
                    'clue' => [
                        'where is the Christ the Redeemer statue located in?',
                        'Eiffel Tower is located in?',
                        'Where is the Leaning Tower of Pisa?'
                    ],
                    'orientation' => [
                        0, 1, 0
                    ],
                    'startrow' => [
                        1, 0, 3
                    ],
                    'startcolumn' => [
                        0, 2, 2
                    ],
                ], []
            ],
            'The letter at the intersection of two words do not match' => [
                [
                    'noanswers' => 3,
                    'answer' => [
                        'AAA', 'BBB', 'CCC'
                    ],
                    'clue' => [
                        'Clue A', 'Clue B', 'Clue C'
                    ],
                    'orientation' => [
                        0, 0, 0
                    ],
                    'startrow' => [
                        0, 0, 0
                    ],
                    'startcolumn' => [
                        0, 0, 0
                    ],
                ],
                [
                    'answeroptions[1]' => get_string('wrongintersection', 'qtype_crossword'),
                    'answeroptions[2]' => get_string('wrongintersection', 'qtype_crossword')
                ]
            ],
            'Requires at least 1 word' => [
              [
                  'noanswers' => 3,
                  'answer' => [
                      '', '', ''
                  ],
                  'clue' => [
                      '', '', ''
                  ],
                  'orientation' => [
                      0, 0, 0
                  ],
                  'startrow' => [
                      0, 0, 0
                  ],
                  'startcolumn' => [
                      0, 0, 0
                  ],
              ], ['answeroptions[0]' => get_string('notenoughwords', 'qtype_crossword', 1)]
            ],
            'The word start or end position is outside the defined grid size' => [
              [
                  'noanswers' => 3,
                  'answer' => [
                      'Toolongtext', 'BBB', 'CCC'
                  ],
                  'clue' => [
                      'Clue A', 'Clue B', 'Clue C'
                  ],
                  'orientation' => [
                      0, 0, 0
                  ],
                  'startrow' => [
                      0, 1, 2
                  ],
                  'startcolumn' => [
                      0, 0, 0
                  ],
              ], ['answeroptions[0]' => get_string('overflowposition', 'qtype_crossword')]
            ],
            'The answer must be alphanumeric characters only' => [
              [
                  'noanswers' => 3,
                  'answer' => [
                      'Speci@al char*', 'BBB', 'CCC'
                  ],
                  'clue' => [
                      'Clue A', 'Clue B', 'Clue C'
                  ],
                  'orientation' => [
                      0, 0, 0
                  ],
                  'startrow' => [
                      0, 1, 2
                  ],
                  'startcolumn' => [
                      0, 0, 0
                  ],
              ], ['answeroptions[0]' => get_string('mustbealphanumeric', 'qtype_crossword')]
            ],
            'The word must have both clues and answers' => [
                [
                    'noanswers' => 3,
                    'answer' => [
                        'AAA', '', 'CCC'
                    ],
                    'clue' => [
                        '', 'Clue B', 'Clue C'
                    ],
                    'orientation' => [
                        0, 0, 0
                    ],
                    'startrow' => [
                        0, 1, 2
                    ],
                    'startcolumn' => [
                        0, 0, 0
                    ],
                ],
                [
                    'answeroptions[0]' => get_string('pleaseenterclueandanswer', 'qtype_crossword', 1),
                    'answeroptions[1]' => get_string('pleaseenterclueandanswer', 'qtype_crossword', 2),
                ]
            ]
        ];
    }

    /**
     * Test editing form validation.
     *
     * @dataProvider test_form_validation_provider
     * @covers \validation
     * @param array $sampledata
     * @param array $expectederror
     */
    public function test_form_validation(array $sampledata, array $expectederror): void {
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $gen = $this->getDataGenerator();
        $course = $gen->create_course();
        $context = \context_course::instance($course->id);

        $contexts = qtype_crossword_test_helper::question_edit_contexts($context);
        $category = question_make_default_categories($contexts->all());

        $question = new \stdClass();
        $question->category = $category->id;
        $question->contextid = $category->contextid;
        $question->qtype = 'crossword';
        $question->createdby = 1;
        $question->questiontext = 'Initial text';
        $question->timecreated = '1234567890';
        $question->formoptions = new \stdClass();
        $question->formoptions->canedit = true;
        $question->formoptions->canmove = true;
        $question->formoptions->cansaveasnew = false;
        $question->formoptions->repeatelements = true;

        $qtypeobj = \question_bank::get_qtype($question->qtype);

        $mform = $qtypeobj->create_editing_form('question.php', $question, $category, $contexts, true);

        $fromform = [
            'category' => 1,
            'name' => 'Test combined with varnumeric',
            'questiontext' => [
                    'text' => 'Test crossword qtype',
                    'format' => 1
            ],
            'generalfeedback' => [
                    'text' => '',
                    'format' => 1
            ],
            'partiallycorrectfeedback' => [
                    'text' => 'Your answer is partially correct.',
                    'format' => 1
            ],
            'shownumcorrect' => 1,
            'incorrectfeedback' => [
                    'text' => 'Your answer is incorrect.',
                    'format' => 1
            ],
            'numcolumns' => 5,
            'numrows' => 7,
            'penalty' => 0.3333333,
            'numhints' => 0,
            'hints' => [],
            'hintshownumcorrect' => [],
            'tags' => 0,
            'id' => 0,
            'inpopup' => 0,
            'cmid' => 0,
            'courseid' => $course->id,
            'returnurl' => '/mod/quiz/edit.php?cmid=0',
            'scrollpos' => 0,
            'appendqnumstring' => '',
            'qtype' => 'crossword',
            'makecopy' => 0,
            'updatebutton' => 'Save changes and continue editing',
        ];
        $fromform = array_merge($fromform, $sampledata);
        $errors = $mform->validation($fromform, []);
        $this->assertEquals($expectederror, $errors);
    }
}

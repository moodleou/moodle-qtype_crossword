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
 * Test helpers for the crossword question type.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * Test helper class for the crossword question type.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_crossword_test_helper extends question_test_helper {

    /**
     * Get test question function.
     *
     * @return array The test question array.
     */
    public function get_test_questions(): array {
        return ['normal', 'unicode', 'different_codepoint', 'sampleimage', 'clear_incorrect_response', 'normal_with_space'];
    }

    /**
     * Makes a normal crossword question.
     *
     * The crossword layout is:
     *
     *     P
     * B R A Z I L
     *     R
     *     I T A L Y
     *     S
     *
     * @return qtype_crossword_question
     */
    public function make_crossword_question_normal() {
        question_bank::load_question_definition_classes('crossword');
        $cw = new qtype_crossword_question();
        test_question_maker::initialise_a_question($cw);
        $cw->name = 'Cross word question';
        $cw->questiontext = 'Cross word question text.';
        $cw->correctfeedback = 'Cross word feedback.';
        $cw->correctfeedbackformat = FORMAT_HTML;
        $cw->penalty = 1;
        $cw->defaultmark = 1;
        $cw->numrows = 5;
        $cw->numcolumns = 7;
        $cw->qtype = question_bank::get_qtype('crossword');
        $answerslist = [
            (object) [
                'id' => 1,
                'questionid' => 1,
                'clue' => 'where is the Christ the Redeemer statue located in?',
                'clueformat' => FORMAT_HTML,
                'answer' => 'BRAZIL',
                'startcolumn' => 0,
                'startrow' => 1,
                'orientation' => 0,
                'feedback' => 'This is correct answer',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 2,
                'questionid' => 1,
                'clue' => 'Eiffel Tower is located in?',
                'clueformat' => FORMAT_HTML,
                'answer' => 'PARIS',
                'startcolumn' => 2,
                'startrow' => 0,
                'orientation' => 1,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 3,
                'questionid' => 1,
                'clue' => 'Where is the Leaning Tower of Pisa?',
                'clueformat' => FORMAT_HTML,
                'answer' => 'ITALY',
                'startcolumn' => 2,
                'startrow' => 3,
                'orientation' => 0,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
        ];

        foreach ($answerslist as $answer) {
            $cw->answers[] = new \qtype_crossword\answer(
                $answer->id,
                $answer->answer,
                $answer->clue,
                $answer->clueformat,
                $answer->orientation,
                $answer->startrow,
                $answer->startcolumn,
                $answer->feedback,
                $answer->feedbackformat,
            );
        }
        return $cw;
    }

    /**
     * Makes a normal crossword question.
     */
    public function get_crossword_question_form_data_normal() {
        $fromform = new stdClass();
        $fromform->name = 'Cross word question';
        $fromform->questiontext = ['text' => 'Crossword question text', 'format' => FORMAT_HTML];
        $fromform->correctfeedback = ['text' => 'Correct feedback', 'format' => FORMAT_HTML];
        $fromform->partiallycorrectfeedback = ['text' => 'Partially correct feedback.', 'format' => FORMAT_HTML];
        $fromform->incorrectfeedback = ['text' => 'Incorrect feedback.', 'format' => FORMAT_HTML];
        $fromform->penalty = 1;
        $fromform->defaultmark = 1;
        $fromform->answer = ['BRAZIL', 'PARIS', 'ITALY'];
        $fromform->clue = [
            [
                'text' => 'where is the Christ the Redeemer statue located in?',
                'format' => FORMAT_HTML
            ],
            [
                'text' => 'Eiffel Tower is located in?',
                'format' => FORMAT_HTML
            ],
            [
                'text' => 'Where is the Leaning Tower of Pisa?',
                'format' => FORMAT_HTML
            ],
        ];
        $fromform->orientation = [0, 1, 0];
        $fromform->startrow = [1, 0, 3];
        $fromform->startcolumn = [0, 2, 2];
        $fromform->numrows = 5;
        $fromform->numcolumns = 7;
        return $fromform;
    }

    /**
     * Makes a normal crossword question with a sample image in question text.
     *
     * @return qtype_crossword_question
     */
    public function get_crossword_question_form_data_sampleimage() {
        $fromform = $this->get_crossword_question_form_data_normal();
        $fromform->correctfeedback = ['text' => 'Correct feedback <img src="@@PLUGINFILE@@/correctfbimg.jpg" />',
            'format' => FORMAT_HTML];
        $fromform->partiallycorrectfeedback = ['text' => 'Partially correct feedback. <img src="@@PLUGINFILE@@/partialfbimg.jpg"',
            'format' => FORMAT_HTML];
        $fromform->incorrectfeedback = ['text' => 'Incorrect feedback. <img src="@@PLUGINFILE@@/incorrectfbimg.jpg"',
            'format' => FORMAT_HTML];
        $fromform->questiontext = [
            'text' => 'Cross word question text with sample image <img src="@@PLUGINFILE@@/questiontextimg.jpg" />',
            'format' => FORMAT_HTML
        ];
        $fromform->feedback = [
            [
                'text' => 'where is the Christ the Redeemer statue located in? <img src="@@PLUGINFILE@@/feedback.jpg" />',
                'format' => FORMAT_HTML
            ],
        ];
        $fromform->clue[0]['text'] = 'where is the Christ the Redeemer statue located in?' .
            '<img src="@@PLUGINFILE@@/clueimg.jpg" />';
        $fromform->feedback[0]['text'] = 'where is the Christ the Redeemer statue located in?' .
            '<img src="@@PLUGINFILE@@/feedback.jpg" />';
        return $fromform;
    }

    /**
     * Makes a unicode crossword question.
     *
     * @return qtype_crossword_question
     */
    public function make_crossword_question_unicode() {
        question_bank::load_question_definition_classes('crossword');
        $cw = new qtype_crossword_question();
        test_question_maker::initialise_a_question($cw);
        $cw->name = 'Cross word question unicode';
        $cw->questiontext = 'Cross word question text unicode.';
        $cw->correctfeedback = 'Cross word feedback unicode.';
        $cw->correctfeedbackformat = FORMAT_HTML;
        $cw->penalty = 1;
        $cw->defaultmark = 1;
        $cw->numrows = 4;
        $cw->numcolumns = 4;
        $cw->qtype = question_bank::get_qtype('crossword');
        $answerslist = [
            (object) [
                'id' => 1,
                'questionid' => 2,
                'clue' => '线索 1',
                'clueformat' => FORMAT_HTML,
                'answer' => '回答一',
                'startcolumn' => 0,
                'startrow' => 2,
                'orientation' => 1,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 2,
                'questionid' => 2,
                'clue' => '线索 2',
                'clueformat' => FORMAT_HTML,
                'answer' => '回答两个',
                'startcolumn' => 0,
                'startrow' => 2,
                'orientation' => 0,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 3,
                'questionid' => 2,
                'clue' => '线索 3',
                'clueformat' => FORMAT_HTML,
                'answer' => '回答三',
                'startcolumn' => 1,
                'startrow' => 1,
                'orientation' => 1,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
        ];

        foreach ($answerslist as $answer) {
            $cw->answers[] = new \qtype_crossword\answer(
                $answer->id,
                $answer->answer,
                $answer->clue,
                $answer->clueformat,
                $answer->orientation,
                $answer->startrow,
                $answer->startcolumn,
                $answer->feedback,
                $answer->feedbackformat,
            );
        }
        return $cw;
    }

    /**
     * Get a unicode crossword question form data.
     */
    public function get_crossword_question_form_data_unicode() {
        $fromform = new stdClass();
        $fromform->name = 'Cross word question unicode';
        $fromform->questiontext = ['text' => 'Crossword question text unicode', 'format' => FORMAT_HTML];
        $fromform->correctfeedback = ['text' => 'Correct feedback', 'format' => FORMAT_HTML];
        $fromform->partiallycorrectfeedback = ['text' => 'Partially correct feedback.', 'format' => FORMAT_HTML];
        $fromform->incorrectfeedback = ['text' => 'Incorrect feedback.', 'format' => FORMAT_HTML];
        $fromform->penalty = 1;
        $fromform->defaultmark = 1;
        $fromform->answer = ['回答一', '回答两个', '回答三'];
        $fromform->clue = [
            [
                'text' => '线索 1',
                'format' => FORMAT_HTML
            ],
            [
                'text' => '线索 2',
                'format' => FORMAT_HTML
            ],
            [
                'text' => '线索 3',
                'format' => FORMAT_HTML
            ],
        ];
        $fromform->orientation = [1, 0, 1];
        $fromform->startrow = [2, 2, 1];
        $fromform->startcolumn = [0, 0, 1];
        $fromform->numrows = 4;
        $fromform->numcolumns = 4;
        return $fromform;
    }

    /**
     * Makes a crossword question has two same answers but different code point.
     *
     * @return qtype_crossword_question
     */
    public function make_crossword_question_different_codepoint() {
        question_bank::load_question_definition_classes('crossword');
        $cw = new qtype_crossword_question();
        test_question_maker::initialise_a_question($cw);
        $cw->name = 'Cross word question different codepoint';
        $cw->questiontext = 'Cross word question text different codepoint.';
        $cw->correctfeedback = 'Cross word feedback different codepoint.';
        $cw->correctfeedbackformat = FORMAT_HTML;
        $cw->penalty = 1;
        $cw->defaultmark = 1;
        $cw->numrows = 6;
        $cw->numcolumns = 6;
        $cw->qtype = question_bank::get_qtype('crossword');
        $answerslist = [
            (object) [
                'id' => 1,
                'questionid' => 2,
                'clue' => 'Answer contains letter é has codepoint \u00e9',
                'clueformat' => FORMAT_HTML,
                'answer' => 'Amélie',
                'startcolumn' => 0,
                'startrow' => 3,
                'orientation' => 0,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 2,
                'questionid' => 2,
                'clue' => 'Answer contains letter é has codepoint \u0065\u0301',
                'clueformat' => FORMAT_HTML,
                'answer' => 'Amélie',
                'startcolumn' => 2,
                'startrow' => 1,
                'orientation' => 1,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
        ];

        foreach ($answerslist as $answer) {
            $cw->answers[] = new \qtype_crossword\answer(
                $answer->id,
                $answer->answer,
                $answer->clue,
                $answer->clueformat,
                $answer->orientation,
                $answer->startrow,
                $answer->startcolumn,
                $answer->feedback,
                $answer->feedbackformat,
            );
        }
        return $cw;
    }

    /**
     * Get a different codepoint crossword question form data.
     */
    public function get_crossword_question_form_data_different_codepoint() {
        $fromform = new stdClass();
        $fromform->name = 'Cross word question different codepoint';
        $fromform->questiontext = ['text' => 'Crossword question text different codepoint', 'format' => FORMAT_HTML];
        $fromform->correctfeedback = ['text' => 'Correct feedback', 'format' => FORMAT_HTML];
        $fromform->partiallycorrectfeedback = ['text' => 'Partially correct feedback.', 'format' => FORMAT_HTML];
        $fromform->incorrectfeedback = ['text' => 'Incorrect feedback.', 'format' => FORMAT_HTML];
        $fromform->penalty = 1;
        $fromform->defaultmark = 1;
        $fromform->answer = ['Amélie', 'Amélie'];
        $fromform->clue = [
            [
                'text' => 'Answer contains letter é has codepoint \u00e9',
                'format' => FORMAT_HTML
            ],
            [
                'text' => 'Answer contains letter é has codepoint \u0065\u0301',
                'format' => FORMAT_HTML
            ],
        ];
        $fromform->orientation = [0, 1];
        $fromform->startrow = [3, 1];
        $fromform->startcolumn = [0, 2];
        $fromform->numrows = 6;
        $fromform->numcolumns = 6;
        return $fromform;
    }

    /**
     * Makes a crossword question with clear incorrect responses option.
     *
     * @return qtype_crossword_question
     */
    public function make_crossword_question_clear_incorrect_response() {
        $cw = $this->make_crossword_question_normal();
        $cw->hints = [
            new question_hint_with_parts(1, 'Hint 1.', FORMAT_HTML, true, true),
            new question_hint_with_parts(2, 'Hint 2.', FORMAT_HTML, true, true),
        ];
        return $cw;
    }

    /**
     * Get a crossword with the clear incorrect response options.
     *
     * @return qtype_crossword_question
     */
    public function get_crossword_question_form_data_clear_incorrect_response() {
        $fromform = $this->get_crossword_question_form_data_normal();
        $fromform->penalty = '0.3333333';
        $fromform->hint = [
            [
                'text' => 'You are wrong.',
                'format' => FORMAT_HTML,
            ],
            [
                'text' => 'You are wrong.',
                'format' => FORMAT_HTML,
            ],
        ];
        $fromform->hintshownumcorrect = [1, 1];
        $fromform->hintclearwrong = [1, 1];
        $fromform->hintoptions = [1, 1];
        return $fromform;
    }

    /**
     * Retrieve the context object.
     * @param \context $context the current context.
     *
     * @return object The context object.
     */
    public static function question_edit_contexts(\context $context): object {
        if (class_exists('\core_question\local\bank\question_edit_contexts')) {
            $contexts = new \core_question\local\bank\question_edit_contexts($context);
        } else {
            $contexts = new \question_edit_contexts($context);
        }
        return $contexts;
    }

    /**
     * Makes a normal crossword question with answer contain spaces.
     *
     * The crossword layout is:
     *
     *        S
     *        A
     *  G R I N C H
     *        T
     *        A
     *
     *    D E C E M B E R
     *        L
     *        A
     *        U
     *        S
     *
     * @return qtype_crossword_question
     */
    public function make_crossword_question_normal_with_space() {
        question_bank::load_question_definition_classes('crossword');
        $cw = new qtype_crossword_question();
        test_question_maker::initialise_a_question($cw);
        $cw->name = 'Cross word question';
        $cw->questiontext = 'Cross word question text.';
        $cw->correctfeedback = 'Cross word feedback.';
        $cw->correctfeedbackformat = FORMAT_HTML;
        $cw->penalty = 1;
        $cw->defaultmark = 1;
        $cw->numrows = 11;
        $cw->numcolumns = 12;
        $cw->qtype = question_bank::get_qtype('crossword');
        $answerslist = [
            (object) [
                'id' => 1,
                'questionid' => 1,
                'clue' => 'Name a man who gave presents to children on Christmas Day?',
                'clueformat' => FORMAT_HTML,
                'answer' => 'SANTA CLAUS',
                'startcolumn' => 3,
                'startrow' => 0,
                'orientation' => 1,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 2,
                'questionid' => 1,
                'clue' => 'What day is Christmas?',
                'clueformat' => FORMAT_HTML,
                'answer' => 'DECEMBER 25',
                'startcolumn' => 1,
                'startrow' => 6,
                'orientation' => 1,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
            (object) [
                'id' => 3,
                'questionid' => 1,
                'clue' => 'Name a fictional character who has green fur and hates Christmas?',
                'clueformat' => FORMAT_HTML,
                'answer' => 'GRINCH',
                'startcolumn' => 0,
                'startrow' => 2,
                'orientation' => 0,
                'feedback' => '',
                'feedbackformat' => FORMAT_HTML,
            ],
        ];

        foreach ($answerslist as $answer) {
            $cw->answers[] = new \qtype_crossword\answer(
                $answer->id,
                $answer->answer,
                $answer->clue,
                $answer->clueformat,
                $answer->orientation,
                $answer->startrow,
                $answer->startcolumn,
                $answer->feedback,
                $answer->feedbackformat,
            );
        }
        return $cw;
    }

    /**
     * Makes a normal crossword question with answer contains space.
     */
    public function get_crossword_question_form_data_normal_with_space() {
        $fromform = new stdClass();
        $fromform->name = 'Cross word question';
        $fromform->questiontext = ['text' => 'Crossword question text', 'format' => FORMAT_HTML];
        $fromform->correctfeedback = ['text' => 'Correct feedback', 'format' => FORMAT_HTML];
        $fromform->partiallycorrectfeedback = ['text' => 'Partially correct feedback.', 'format' => FORMAT_HTML];
        $fromform->incorrectfeedback = ['text' => 'Incorrect feedback.', 'format' => FORMAT_HTML];
        $fromform->penalty = 1;
        $fromform->defaultmark = 1;
        $fromform->answer = ['SANTA CLAUS', 'DECEMBER 25', 'GRINCH'];
        $fromform->clue = [
            [
                'text' => 'Name a man who gave presents to children on Christmas Day?',
                'format' => FORMAT_HTML
            ],
            [
                'text' => 'What day is Christmas?',
                'format' => FORMAT_HTML
            ],
            [
                'text' => 'Name a fictional character who has green fur and hates Christmas?',
                'format' => FORMAT_HTML
            ],
        ];
        $fromform->orientation = [1, 0, 0];
        $fromform->startrow = [0, 6, 2];
        $fromform->startcolumn = [3, 1, 0];
        $fromform->numrows = 11;
        $fromform->numcolumns = 12;
        return $fromform;
    }
}

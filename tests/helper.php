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

/**
 * Test helper class for the crossword question type.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_crossword_test_helper extends question_test_helper {
    public function get_test_questions() {
        return ['normal'];
    }

    /**
     * Makes a normal crossword question.
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
        $cw->qtype = question_bank::get_qtype('crossword');
        $answerobject = new \qtype_crossword\answer();
        $answerobject->numrows = 5;
        $answerobject->numcolumns = 7;
        $answers = [
            (object) [
                'id' => 1,
                'questionid' => 1,
                'clue' => 'where is the Christ the Redeemer statue located in?',
                'answer' => 'BRAZIL',
                'startcolumn' => 0,
                'startrow' => 1,
                'orientation' => 0,
            ],
            (object) [
                'id' => 2,
                'questionid' => 1,
                'clue' => 'Eiffel Tower is located in?',
                'answer' => 'PARIS',
                'startcolumn' => 2,
                'startrow' => 0,
                'orientation' => 1,
            ],
            (object) [
                'id' => 3,
                'questionid' => 1,
                'clue' => 'Where is the Leaning Tower of Pisa?',
                'answer' => 'ITALY',
                'startcolumn' => 2,
                'startrow' => 3,
                'orientation' => 0,
            ],
        ];
        $cw->answers = $answerobject->create_from_data($answers);
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
            'where is the Christ the Redeemer statue located in?',
            'Eiffel Tower is located in?',
            'Where is the Leaning Tower of Pisa?'
        ];
        $fromform->orientation = [0, 1, 0];
        $fromform->startrow = [1, 0, 3];
        $fromform->startcolumn = [0, 2, 2];
        $fromform->numrows = 5;
        $fromform->numcolumns = 7;
        return $fromform;
    }

}

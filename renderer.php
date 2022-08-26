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
 * Crossword question renderer class.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Generates the output for crossword questions.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_crossword_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa,
        question_display_options $options): string {
        $question = $qa->get_question();
        $response = $qa->get_last_qt_data();
        $data = [];
        $orientationvalue = [
            get_string('across', 'qtype_crossword'),
            get_string('down', 'qtype_crossword')
        ];
        $selected = 0;
        $binddata = [
            'colsNum' => $question->answers->numcolumns + 3,
            'rowsNum' => $question->answers->numrows + 3,
            'isPreview' => false,
            'title' => get_string('celltitle', 'qtype_crossword'),
            'orientation' => $orientationvalue,
            'words' => [],
            'readonly' => false
        ];
        $data['questiontext'] = $question->questiontext;
        for ($i = 0; $i < count($question->answers->answer); $i++) {
            $orientation = 'across';
            $fieldname = 'sub' . $i;
            $length = mb_strlen($question->answers->answer[$i]);
            $inputname = $qa->get_qt_field_name($fieldname);
            $inputvalue = $qa->get_last_qt_var($fieldname);
            $number = $i + 1;
            $clue = $question->answers->clue[$i];
            $title = get_string(
                'inputtitle',
                'qtype_crossword',
                (object) [
                    'number' => $number,
                    'orientation' => $orientationvalue[$question->answers->orientation[$i]],
                    'clue' => $clue,
                    'length' => $length
                ]
            );
            if ($question->answers->orientation[$i]) {
                $orientation = 'down';
            }

            $attributes = "name=$inputname id=$inputname maxlength=$length";

            $inputdata = [
                'attribute' => '',
                'number' => $number,
                'clue' => $clue,
                'length' => $length,
                'value' => $inputvalue,
                'attributes' => $attributes,
                'title' => $title,
                'id' => $inputname
            ];

            $binddata['words'][] = [
                'number' => $number,
                'clue' => $clue,
                'startRow' => (int) $question->answers->startrow[$i],
                'startColumn' => (int) $question->answers->startcolumn[$i],
                'length' => mb_strlen($question->answers->answer[$i]),
                'orientation' => (int) $question->answers->orientation[$i]
            ];

            if ($options->readonly) {
                $binddata['readonly'] = true;
                $inputdata['attributes'] .= ' readonly=true';
            }

            if (array_key_exists($fieldname, $response)) {
                $selected = $response[$fieldname];
            }

            $fraction = (int) ($selected && $selected === $question->answers->answer[$i]);
            if ($options->correctness) {
                $inputdata['classes'] = $this->feedback_class($fraction);
                $inputdata['feedbackimage'] = $this->feedback_image($fraction);
            }

            $data[$orientation][] = $inputdata;
        }

        if ($qa->get_state() === question_state::$invalid) {
            $data['invalidquestion'] = $question->get_validation_error($qa->get_last_qt_data());
        }

        $result = $this->render_from_template('qtype_crossword/crossword_clues', $data);

        $this->page->requires->js_call_amd('qtype_crossword/crossword', 'attempt', [$binddata]);
        return $result;
    }

    public function specific_feedback(question_attempt $qa): string {
        return $this->combined_feedback($qa);
    }

    public function correct_response(question_attempt $qa): string {
        $question = $qa->get_question();
        $right = [];
        foreach ($question->answers->answer as $ansid => $ans) {
            $right[] = $question->make_html_inline($question->format_text($ans, 1,
                $qa, 'question', 'answer', $ansid));
        }
        return $this->correct_choices($right);
    }

    /**
     * Function returns string based on number of correct answers
     * @param array $right An Array of correct responses to the current question
     * @return string based on number of correct responses
     */
    protected function correct_choices(array $right): ?string {
        // Return appropriate string for single/multiple correct answer(s).
        $stringright = '';

        if (count($right) < 1) {
            return '';
        }

        foreach ($right as $key => $value) {
            $stringright .= get_string('answer', 'qtype_crossword') . ' ' . ($key + 1) .': '. $value;
            if ($key !== count($right) - 1) {
                $stringright .= ', ';
            }
        }

        if (count($right) === 1) {
            return get_string('correctansweris', 'qtype_crossword',
                $stringright);
        }

        return get_string('correctanswersare', 'qtype_crossword',
            $stringright);
    }

    protected function num_parts_correct(question_attempt $qa): ?string {
        $a = new stdClass();
        list($a->num, $a->outof) = $qa->get_question()->get_num_parts_right(
            $qa->get_last_qt_data());
        if (is_null($a->outof)) {
            return '';
        } else if ($a->num === 1) {
            return get_string('yougot1right', 'qtype_crossword');
        } else {
            return get_string('yougotnright', 'qtype_crossword', $a);
        }
    }
}

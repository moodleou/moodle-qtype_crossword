<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * The editing form for crossword question type is defined here.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Crossword question editing form definition.
 *
 * You should override functions as necessary from the parent class located at
 * /question/type/edit_question_form.php.
 */
class qtype_crossword_edit_form extends question_edit_form {

    /** @var int Number of rows. */
    protected $numrows;
    /** @var int Number of columns. */
    protected $numcolumns;
    /** @var array The grid options. */
    protected $gridoptions;

    protected function definition_inner($mform): void {
        // Set grid options.
        $this->gridoptions = range(3, 15);
        // Add grid height field.
        $mform->addElement('select', 'numrows',
            get_string('numberofrows', 'qtype_crossword'), $this->gridoptions, null);
        $mform->addRule('numrows', null, 'required', null, 'client');
        $mform->setDefault('numrows', 4);

        // Add grid width field.
        $mform->addElement('select', 'numcolumns',
            get_string('numberofcolumns', 'qtype_crossword'), $this->gridoptions, null);
        $mform->addRule('numcolumns', null, 'required', null, 'client');
        $mform->setDefault('numcolumns', 4);
        // Add update field.
        $mform->addElement('submit', 'updateform', get_string('updateform', 'qtype_crossword'));
        $mform->registerNoSubmitButton('updateform');

        $this->set_current_grid_setting();
        $this->add_question_section($mform);

        $this->add_combined_feedback_fields(true);
        $this->add_interactive_settings(true, true);
    }

    protected function get_per_answer_fields($mform, $label, $gradeoptions,
            &$repeatedoptions, &$wordsoptions): array {
        $repeated = [];
        $wordoptions = [];

        // Add answer field.
        $wordoptions[] = $mform->createElement('text', 'answer',
            get_string('answer', 'qtype_crossword'), ['size' => 20, 'maxlength' => 99, 'class' => 'answer-clue']);
        $mform->setType('answer', PARAM_RAW);

        // Add clue field.
        $wordoptions[] = $mform->createElement('text', 'clue',
            get_string('clue', 'qtype_crossword'), ['size' => 40, 'class' => 'clue-label']);
        $mform->setType('clue', PARAM_RAW);

        // Add group.
        $repeated[] = $mform->createElement('group', 'answeroptions',
            $label, $wordoptions, null, false);

        $wordoptions = [];

        // Add Orientation selection.
        $wordoptions[] = $mform->createElement(
            'select',
            'orientation',
            get_string('orientation', 'qtype_crossword'),
            [
                get_string('across', 'qtype_crossword'),
                get_string('down', 'qtype_crossword')
            ],
            null
        );
        $mform->setType('orientation', PARAM_INT);

        // Add coordinates form.
        $coodinatesform = $this->add_coordinates_input($mform);

        $wordoptions = array_merge($wordoptions, $coodinatesform);

        $repeated[] = $mform->createElement('group', 'coodinateoptions',
            '', $wordoptions, null, false);

        $repeatedoptions['answer']['type'] = PARAM_RAW;
        $repeatedoptions['clue']['type'] = PARAM_RAW;
        $wordsoptions = 'words';
        return $repeated;
    }

    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
        $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $mform->addElement('header', 'words',
            get_string('words', 'qtype_crossword'), '');
        $mform->setExpanded('words', 1);
        $answersoption = '';
        $repeatedoptions = [];
        $repeated = $this->get_per_answer_fields($mform, $label, $gradeoptions,
            $repeatedoptions, $answersoption);

        if (isset($this->question->options)) {
            $repeatsatstart = count($this->question->options->$answersoption);
        } else {
            $repeatsatstart = $minoptions;
        }

        $this->repeat_elements($repeated, $repeatsatstart, $repeatedoptions,
            'noanswers', 'addanswers', $addoptions,
            $this->get_more_choices_string(), true);
    }

    protected function get_more_choices_string() {
        return get_string('addmorewordblanks', 'qtype_crossword');
    }

    /**
     * Set the grid size.
     *
     * @return void
     */
    protected function set_current_grid_setting(): void {
        $numrowsindex = optional_param('numrows', -1, PARAM_INT);
        $numcolumnsindex = optional_param('numcolumns', -1, PARAM_INT);

        if ($numrowsindex < 0) {
            $numrowsindex = $this->question->options->numrows ?? 4;
        }

        if ($numcolumnsindex < 0) {
            $numcolumnsindex = $this->question->options->numcolumns ?? 4;
        }

        $this->numrows = $this->gridoptions[$numrowsindex] ?? 4;
        $this->numcolumns = $this->gridoptions[$numcolumnsindex] ?? 4;
    }

    /**
     * Add the question elements.
     *
     * @param object $mform The form being built.
     * @return void.
     */
    protected function add_question_section(object $mform): void {
        global $PAGE;

        if ($this->numcolumns < 1 || $this->numrows < 1) {
            return;
        }

        // Add header Preview.
        $mform->addElement('header', 'previewhdr', get_string('preview', 'qtype_crossword'));
        $mform->setExpanded('previewhdr', 0);
        $mform->registerNoSubmitButton('refresh');
        $mform->addElement('button', 'refresh', get_string('preview', 'qtype_crossword'), ['disabled' => 'disabled']);

        // Add preview section.
        $mform->addElement('html', '<div class="crossword-contain" id="crossword"></div>');
        // Call js to render preview section.
        $options = new stdClass();
        $options->element = '#id_refresh';
        $options->target = '#crossword';
        $options->isPreview = true;
        $PAGE->requires->js_call_amd('qtype_crossword/crossword', 'preview', [$options]);

        $this->add_per_answer_fields($mform, get_string('wordno', 'qtype_crossword', '{no}'), question_bank::fraction_options());
        $mform->addHelpButton('words', 'words', 'qtype_crossword');
    }

    /**
     * Add coordinates for cells.
     *
     * @param object $mform The form being built.
     * @return array Elements rows index and columns index.
     */
    protected function add_coordinates_input(object $mform): array {
        $alphabetrange = range('A', 'Z');
        $numberrange = range(1, 100);
        $repeated = [];

        $columnoptions = array_slice($alphabetrange, 0, $this->numcolumns);
        $rowoptions = array_slice($numberrange, 0, $this->numrows);

        // Add row index field.
        $repeated[] = $mform->createElement('select', 'startrow', get_string('startrow', 'qtype_crossword'), $rowoptions);
        $mform->setType('startrow', PARAM_INT);

        // Add column index field.
        $repeated[] = $mform->createElement('select', 'startcolumn', get_string('startcolumn', 'qtype_crossword'), $columnoptions);
        $mform->setType('startcolumn', PARAM_INT);

        return $repeated;
    }

    protected function data_preprocessing($question): object {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);
        $question = $this->data_preprocessing_words($question);
        return $question;
    }

    /**
     * Custom question data for words.
     *
     * @param object $question The question object.
     * @return object The custom question object.
     */
    private function data_preprocessing_words(object $question): object {
        global $DB;
        $answer = [];
        $clue = [];
        $orientation = [];
        $startrow = [];
        $startcolumn = [];
        $answers = [];
        if (isset($question->id)) {
            $answers = $DB->get_records('qtype_crossword_words', ['questionid' => $question->id], 'id ASC');
        }
        if (!empty($answers)) {
            foreach ($answers as $answerdata) {
                $answer[] = $answerdata->answer;
                $clue[] = $answerdata->clue;
                $orientation[] = $answerdata->orientation;
                $startrow[] = $answerdata->startrow;
                $startcolumn[] = $answerdata->startcolumn;
            }
        }

        if (!empty($question->options)) {
            $question->numrows = $question->options->numrows;
            $question->numcolumns = $question->options->numcolumns;
        }

        $question->answer = $answer;
        $question->clue = $clue;
        $question->orientation = $orientation;
        $question->startrow = $startrow;
        $question->startcolumn = $startcolumn;
        return $question;
    }

    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $answercount = 0;
        $answers = $data['answer'];
        $clues = $data['clue'];
        // phpcs:ignore
        $regex = '/[-@!$%^&*()_+|~=`\\#{}\[\]:";\'<>?,.\/]/';
        $except = [];
        for ($i = 0; $i < count($answers); $i++) {
            // Skip the invalid word.
            $clue = trim($clues[$i]);
            $answer = trim($answers[$i]);
            if ($clue === '' || $answer === '') {
                if ($clue === $answer) {
                    continue;
                }
                $errors["answeroptions[$i]"] = get_string('pleaseenterclueandanswer', 'qtype_crossword', $i + 1);
            }
            $answercount++;

            // Check alphanumeric letter.
            if (!isset($errors["answeroptions[$i]"]) && preg_match($regex, strtolower($answers[$i]))) {
                $errors["answeroptions[$i]"] = get_string('mustbealphanumeric', 'qtype_crossword');
            }

            // Check answer length.
            if (!(isset($errors["answeroptions[$i]"]) || $this->check_word_length($data, $i))) {
                $errors["answeroptions[$i]"] = get_string('overflowposition', 'qtype_crossword');
            }
            // Check clue length.
            if (!isset($errors["answeroptions[$i]"]) && mb_strlen($clue) > 200) {
                $errors["answeroptions[$i]"] = get_string('cluetoolong', 'qtype_crossword');
            }
            if (!isset($errors["answeroptions[$i]"])) {
                $except[] = $i;
                // Find conflicting words.
                $positions = $this->get_word_conflict($data, $i, $except);
                if ($positions) {
                    foreach ($positions as $position) {
                        $errors["answeroptions[$position]"] = get_string('wrongintersection', 'qtype_crossword');
                    }
                }
            }
        }

        if ($answercount < 1) {
            $errors['answeroptions[0]'] = get_string('notenoughwords', 'qtype_crossword', 1);
        }

        return $errors;
    }

    /**
     * Check word length with grid's size.
     *
     * @param array $data The question data.
     * @param int $iteral The iteral.
     *
     * @return bool
     */
    private function check_word_length(array $data, int $iteral): bool {
        $answerlength = mb_strlen(trim($data['answer'][$iteral]));
        $orientation = (int) $data['orientation'][$iteral];
        $griddata = range(3, 15);
        $startrow = $data['startrow'][$iteral] ?? null;
        $startcolumn = $data['startcolumn'][$iteral] ?? null;

        if (is_null($startrow) || is_null($startcolumn)) {
            return false;
        }

        // Set default real length.
        $reallength = $answerlength + (int) $startcolumn;
        // Allow length is the number of columns or rows.
        $allowlength = $griddata[$data['numcolumns']];
        // Based on the orientation, we will calculate the real word length.
        if ($orientation) {
            $reallength = $answerlength + (int) $startrow;
            $allowlength = $griddata[$data['numrows']];
        }
        return ($reallength <= $allowlength);
    }

    /**
     * Get conflict words.
     *
     * @param array $data The question data.
     * @param int $iteral The iterated.
     * @param array $except The except list.
     *
     * @return array The conflict positions.
     */
    private function get_word_conflict(array $data, int $iteral, array &$except): array {
        $answer1 = trim(strtolower($data['answer'][$iteral]));
        $positions = [];
        $startrow = $data['startrow'][$iteral] ?? null;
        $startcolumn = $data['startcolumn'][$iteral] ?? null;

        if (is_null($startrow) || is_null($startcolumn)) {
            return $positions;
        }

        // Get the coordinates of the first word.
        $line1 = $this->detect_word_coordinate(
            $startrow,
            $startcolumn,
            $answer1,
            $data['orientation'][$iteral]
        );
        // Compare the first word with another word.
        for ($i = count($data['answer']) - 1; $i >= 0; $i--) {
            $answer2 = trim(strtolower($data['answer'][$i]));
            $clues = trim(strtolower($data['clue'][$i]));
            // Skip invalid word.
            if ($answer2 === "" || $clues === "") {
                $except[] = $i;
                continue;
            }
            // Ignore checked words and invalid word.
            if (in_array($i, $except) || is_null($data['startrow'][$i]) || is_null($data['startcolumn'][$i])) {
                continue;
            }
            // Get the word's coordinates .
            $line2 = $this->detect_word_coordinate(
                $data['startrow'][$i],
                $data['startcolumn'][$i],
                $answer2,
                $data['orientation'][$i]
            );
            $lines = array_merge($line1, $line2);
            // Get intersect point between 2 lines.
            if ($intersects = $this->get_intersect_points($lines, $data['orientation'][$iteral])) {
                foreach ($intersects as $intersect) {
                    if ($data['orientation'][$iteral]) {
                        $character1 = $answer1[$intersect[1] - $data['startrow'][$iteral]] ?? '';
                    } else {
                        $character1 = $answer1[$intersect[0] - $data['startcolumn'][$iteral]] ?? '';
                    }

                    if ($data['orientation'][$i]) {
                        $character2 = $answer2[$intersect[1] - $data['startrow'][$i]] ?? '';
                    } else {
                        $character2 = $answer2[$intersect[0] - $data['startcolumn'][$i]] ?? '';
                    }
                    // Compare letters.
                    if ($character1 !== $character2) {
                        if ($i > $iteral) {
                            $positions[] = $i;
                        } else {
                            $positions[] = $iteral;
                        }
                    }
                }
            }
        }
        return $positions;
    }

    /**
     * Retrieve the coordinate of word.
     * It's an array contains the coordinates of this word.
     *
     * @param string $startrow The row index data.
     * @param string $startcolumn The column index data.
     * @param string $anwser The answer data.
     * @param string $orientation The orientation.
     *
     * @return array The coordinate data [x1, y1, x2, y2].
     */
    private function detect_word_coordinate(string $startrow, string $startcolumn, string $anwser, string $orientation): array {
        $x1 = (int) $startcolumn;
        $y1 = (int) $startrow;
        // Retrieve the answer length.
        $anwserlength = mb_strlen(trim($anwser)) - 1;
        // Set the default coordinate for the second point.
        $x2 = $anwserlength + $x1;
        $y2 = $y1;
        // If the orientation is down, we will change the coordinate of second point.
        if ($orientation) {
            $x2 = $x1;
            $y2 = $anwserlength + $y1;

        }
        return [$x1, $y1, $x2, $y2];
    }

    /**
     * Get intersection between lines.
     *
     * @param array $lines The coordinate data.
     * @param string $orientation The orientation.
     *
     * @return array The list intersection points.
     */
    private function get_intersect_points(array $lines, string $orientation): array {
        list ($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4) = $lines;

        // Check if the first coordinate is the point.
        if ($x1 === $x2 && $y1 === $y2) {
            // Check if the point belong to the line.
            if (
                ($y1 === $y3 && $x1 >= $x3 && $x1 <= $x4) ||
                ($x1 === $x3 && $y1 >= $y3 && $y1 <= $y4)
            ) {
                return [[$x1, $y1]];
            }
            return [];
        }
        // Check if the second coordinate is the point.
        if ($x3 === $x4 && $y3 === $y4) {
            // Check if the point belong to the line.
            if (
                ($y3 === $y1 && $x3 >= $x1 && $x3 <= $x2) ||
                ($x3 === $x1 && $y3 >= $y1 && $y3 <= $y2)
            ) {
                return [[$x3, $y3]];
            }
            return [];
        }
        $denominator = ($y4 - $y3) * ($x2 - $x1) - ($x4 - $x3) * ($y2 - $y1);
        $numerator1 = ($x4 - $x3) * ($y1 - $y3) - ($y4 - $y3) * ($x1 - $x3);
        $numerator2 = ($x2 - $x1) * ($y1 - $y3) - ($y2 - $y1) * ($x1 - $x3);

        if ($denominator === 0) {
            // Lines are coincident.
            if ($numerator1 === 0 && $numerator2 === 0) {
                // Find all the common points of 2 lines.
                return $this->find_multi_intersect_points($lines, $orientation);
            }
            return [];
        }
        $ua = (($x4 - $x3) * ($y1 - $y3) - ($y4 - $y3) * ($x1 - $x3)) / $denominator;
        $ub = (($x2 - $x1) * ($y1 - $y3) - ($y2 - $y1) * ($x1 - $x3)) / $denominator;

        // Is the intersection along the segments.
        if ($ua < 0 || $ua > 1 || $ub < 0 || $ub > 1) {
            return [];
        }
        // Return an array with the x and y coordinates of the intersection.
        $x = (int) ($x1 + $ua * ($x2 - $x1));
        $y = (int) ($y1 + $ua * ($y2 - $y1));
        return [[$x, $y]];
    }

    /**
     * Get intersection points for coincident lines.
     *
     * @param array $lines The coordinate data.
     * @param string $orientation The orientation.
     *
     * @return array The list intersection points.
     */
    private function find_multi_intersect_points(array $lines, string $orientation): array {
        list ($x1, $y1, $x2, $y2, $x3, $y3, $x4, $y4) = $lines;
        // Lines are coincident.
        $from = max($x1, $x3);
        $to = min($x2, $x4);
        $points = [];
        if ($orientation) {
            $from = max($y1, $y3);
            $to = min($y2, $y4);
        }
        for ($i = $from; $i <= $to; $i++) {
            $point = [$i, $y1];
            if ($orientation) {
                $point = [$x1, $i];
            }
            $points[] = $point;
        }
        return $points;
    }


    /**
     * Returns the question type name.
     *
     * @return string The question type name.
     */
    public function qtype(): string {
        return 'crossword';
    }
}

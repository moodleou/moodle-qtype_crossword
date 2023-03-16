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
        $this->gridoptions = range(3, 30);
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
        $coordinatesoptions = [];

        // Add Orientation selection.
        $coordinatesoptions[] = $mform->createElement(
            'select',
            'orientation',
            get_string('orientation', 'qtype_crossword'),
            [
                get_string('across', 'qtype_crossword'),
                get_string('down', 'qtype_crossword')
            ],
            null
        );
        $coordinatesoptions = array_merge($coordinatesoptions, $this->add_coordinates_input($mform));
        $mform->setType('orientation', PARAM_INT);

        $repeated[] = $mform->createElement('group', 'coodinateoptions',
            $label, $coordinatesoptions, null, false);

        // Add answer field.
        $repeated[] = $mform->createElement('text', 'answer',
            get_string('answer', 'qtype_crossword'), ['size' => 20, 'maxlength' => 99, 'class' => 'answer-clue']);
        $mform->setType('answer', PARAM_RAW);

        // Add clue field.
        $repeated[] = $mform->createElement('editor', 'clue',
            get_string('clue', 'qtype_crossword'), ['rows' => 1], $this->editoroptions);
        $mform->setType('clue', PARAM_RAW);

        // Add feedback field.
        $repeated[] = $mform->createElement('editor', 'feedback',
            get_string('feedback', 'question'), ['rows' => 1], $this->editoroptions);
        $mform->setType('feedback', PARAM_RAW);

        $wordsoptions = 'words';
        return $repeated;
    }

    protected function add_per_answer_fields(&$mform, $label, $gradeoptions,
        $minoptions = QUESTION_NUMANS_START, $addoptions = QUESTION_NUMANS_ADD) {
        $mform->addElement('header', 'words',
            get_string('words', 'qtype_crossword'), '');

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
        $mform->addElement('html', '<div class="crossword-contain mx-3" id="crossword"></div>');

        // Add answer options.
        $mform->addElement('header', 'answeroptionsheader', get_string('answeroptions', 'qtype_crossword'));
        $mform->setExpanded('answeroptionsheader', 0);
        $optionsaccented = [
            get_string('mustmatchexactly', 'qtype_crossword'),
            get_string('deductmarksforwrongpunctuation', 'qtype_crossword'),
            get_string('acceptwrongaccents', 'qtype_crossword'),
        ];
        $mform->addElement('select', 'accentedlettersoptions', get_string('accentedcharacters', 'qtype_crossword'),
            $optionsaccented);
        $mform->setDefault('accentedlettersoptions', $this->get_default_value('accentedlettersoptions', 0));
        $penaltyoptions = question_bank::fraction_options();
        // Remove None and 100%.
        unset($penaltyoptions['0.0']);
        unset($penaltyoptions['1.0']);
        $mform->addElement('select', 'penaltyforincorrectaccents',
            get_string('penaltyforincorrectaccents', 'qtype_crossword'), $penaltyoptions);
        $mform->setDefault('penaltyforincorrectaccents', $this->get_default_value('penaltyforincorrectaccents',  0.5));
        $mform->hideIf('penaltyforincorrectaccents', 'accentedlettersoptions', 'noteq', 1);

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
        $numberrange = range(1, 100);
        $repeated = [];

        $columnoptions = $this->generate_alphabet_list(0, $this->numcolumns);
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
        $answer = [];
        $clue = [];
        $orientation = [];
        $startrow = [];
        $startcolumn = [];
        $feedback = [];
        if (!empty($question->options->words)) {
            $key = 0;
            foreach ($question->options->words as $index => $answerdata) {
                // Prepare the clue editor to display files in draft area.
                $answer[] = $answerdata->answer;
                $cluedraftitemid = file_get_submitted_draft_itemid('clue['.$key.']');
                $itemid = (int)$answerdata->id ?? null;
                $clue[$key]['text'] = file_prepare_draft_area(
                    $cluedraftitemid,
                    $this->context->id,
                    'question',
                    'clue',
                    $itemid,
                    $this->fileoptions,
                    $answerdata->clue
                );
                $clue[$key]['itemid'] = $cluedraftitemid;
                $clue[$key]['format'] = $answerdata->clueformat ?? FORMAT_HTML;
                $question->options->words[$index]->clueformat = $clue[$key]['format'];
                $question->options->words[$index]->clue = $clue[$key]['text'];

                // Prepare the feedback editor to display files in draft area.
                $feedbackdraftitemid = file_get_submitted_draft_itemid('feedback['.$key.']');
                $feedback[$key]['text'] = file_prepare_draft_area(
                    $feedbackdraftitemid,
                    $this->context->id,
                    'question',
                    'feedback',
                    $itemid,
                    $this->fileoptions,
                    $answerdata->feedback
                );
                $feedback[$key]['itemid'] = $feedbackdraftitemid;
                $feedback[$key]['format'] = $answerdata->feedbackformat ?? FORMAT_HTML;
                $question->options->words[$index]->feedbackformat = $feedback[$key]['format'];
                $question->options->words[$index]->feedback = $feedback[$key]['text'];

                $orientation[] = $answerdata->orientation;
                $startrow[] = $answerdata->startrow;
                $startcolumn[] = $answerdata->startcolumn;
                $key++;
            }
        }
        if (!empty($question->options)) {
            $question->numrows = $question->options->numrows;
            $question->numcolumns = $question->options->numcolumns;
            $question->accentedlettersoptions = $question->options->accentedlettersoptions;
            $question->penaltyforincorrectaccents = $question->options->penaltyforincorrectaccents;
        }
        $question->answer = $answer;
        $question->clue = $clue;
        $question->feedback = $feedback;
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
            $clues[$i]['text'] = trim($clues[$i]['text']);
            // Normalize answer.
            $answer = \qtype_crossword\util::safe_normalize(trim($answers[$i]));
            if ($clues[$i]['text'] === $answer) {
                continue;
            }
            if ($clues[$i]['text'] === '') {
                $errors["clue[$i]"] = get_string('pleaseenterclueandanswer', 'qtype_crossword', $i + 1);
            }
            if ($answer === '') {
                $errors["answer[$i]"] = get_string('pleaseenterclueandanswer', 'qtype_crossword', $i + 1);
            }
            $answercount++;

            // Check alphanumeric letter.
            if (!isset($errors["answer[$i]"]) && preg_match($regex, core_text::strtolower($answer))) {
                $errors["answer[$i]"] = get_string('mustbealphanumeric', 'qtype_crossword');
            }

            // Check answer length.
            if (!(isset($errors["answer[$i]"]) || $this->check_word_length($data, $i))) {
                $errors["answer[$i]"] = get_string('overflowposition', 'qtype_crossword');
            }
            if (!isset($errors["answer[$i]"])) {
                $except[] = $i;
                // Find conflicting words.
                $positions = $this->get_word_conflict($data, $i, $except);
                if ($positions) {
                    foreach ($positions as $position) {
                        $errors["answer[$position]"] = get_string('wrongintersection', 'qtype_crossword');
                    }
                }
            }
        }

        if ($answercount < 1) {
            $errors['answer[0]'] = get_string('notenoughwords', 'qtype_crossword', 1);
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
        // Normalize answer.
        $answer = \qtype_crossword\util::safe_normalize(trim($data['answer'][$iteral]));
        $answerlength = core_text::strlen($answer);
        $orientation = (int) $data['orientation'][$iteral];
        $griddata = range(3, 30);
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
        $answer1 = \qtype_crossword\util::safe_normalize(trim(core_text::strtolower($data['answer'][$iteral])));
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
            $answer2 = \qtype_crossword\util::safe_normalize(trim(core_text::strtolower($data['answer'][$i])));
            $clues = trim(core_text::strtolower($data['clue'][$i]['text']));
            // Skip invalid word.
            if ($answer2 === "" || $clues === "") {
                $except[] = $i;
                continue;
            }
            // Ignore checked words and invalid word.
            if (in_array($i, $except) || !isset($data['startrow'][$i]) || !isset($data['startcolumn'][$i])) {
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
                        $character1 = core_text::substr($answer1, $intersect[1] - $data['startrow'][$iteral], 1) ?? '';
                    } else {
                        $character1 = core_text::substr($answer1, $intersect[0] - $data['startcolumn'][$iteral], 1) ?? '';
                    }

                    if ($data['orientation'][$i]) {
                        $character2 = core_text::substr($answer2, $intersect[1] - $data['startrow'][$i], 1) ?? '';
                    } else {
                        $character2 = core_text::substr($answer2, $intersect[0] - $data['startcolumn'][$i], 1) ?? '';
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
        $anwserlength = core_text::strlen(trim(\qtype_crossword\util::safe_normalize($anwser))) - 1;
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
     * Generate the alphabet list in the range.
     *
     * @param int $start The start number.
     * @param int $length The range length.
     *
     * @return array The alphabet list,
     * In case index number higher than 25,
     * we will add one letter before the current one like Excel: AA, AB, AC, AD, AE etc.
     */
    private function generate_alphabet_list(int $start, int $length): array {
        $range = range('A', 'Z');
        if ($length <= 26) {
            return array_slice($range, $start, $length);
        }
        $remain = $length - 26;
        $addition = [];
        $j = 0;
        for ($i = 1; $i <= $remain; $i++) {
            if (!isset($range[$j])) {
                $j = 0;
            }
            $addition[] = $range[ceil($i / 26) - 1] . $range[$j];
            $j++;
        }
        return array_merge($range, $addition);
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

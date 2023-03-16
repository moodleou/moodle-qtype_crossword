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
 * Question definition class for crossword.
 *
 * @package     qtype_crossword
 * @copyright   The Open University
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// For a complete list of base question classes please examine the file
// /question/type/questionbase.php.
//
// Make sure to implement all the abstract methods of the base class.
use qtype_crossword\answer;

/**
 * Class that represents a crossword question.
 */
class qtype_crossword_question extends question_graded_automatically {

    /** @var answer[] The answers object. */
    public $answers;

    /** @var int The row number. */
    public $numrows;

    /** @var int The column number. */
    public $numcolumns;

    /** @var string The accented grading type. One of the qtype_crossword::ACCENT_GRADING_... constants. */
    public $accentgradingtype;

    /** @var float The penalty mark for each incorrect accents. */
    public $accentpenalty;

    /**
     * Answer field name.
     *
     * @param int $key Key number.
     * @return string The answer key name.
     */
    protected function field(int $key): string {
        return 'sub' . $key;
    }

    public function get_expected_data(): array {
        $response = [];
        for ($i = 0; $i < count($this->answers); $i++) {
            $response[$this->field($i)] = PARAM_RAW_TRIMMED;
        }
        return $response;
    }

    public function get_correct_response(): ?array {
        $response = [];
        $answers = $this->answers;
        for ($i = 0; $i < count($answers); $i++) {
            $response[$this->field($i)] = $this->answers[$i]->answer;
        }
        return $response;
    }

    public function summarise_response(array $response): ?string {
        $selectedchoices = [];
        foreach ($response as $answer) {
            $selectedchoices[] = str_replace('_', ' ', $answer);
        }
        if (empty($selectedchoices)) {
            return null;
        }
        return implode('; ', $selectedchoices);
    }

    public function is_complete_response(array $response): bool {
        $filteredresponse = $this->filter_answers($response);
        return count($this->answers) === count($filteredresponse);
    }

    public function is_gradable_response(array $response): bool {
        $filteredresponse = $this->filter_answers($response);
        return count($filteredresponse) > 0;
    }

    public function get_validation_error(array $response): string {
        if ($this->is_complete_response($response)) {
            return '';
        }
        return get_string('pleaseananswerallparts', 'qtype_crossword');
    }

    public function is_same_response(array $prevresponse, array $newresponse): bool {
        foreach ($this->answers as $key => $notused) {
            $fieldname = $this->field($key);
            if (!question_utils::arrays_same_at_key(
                $prevresponse, $newresponse, $fieldname)) {
                return false;
            }
        }
        return true;
    }

    public function grade_response(array $response): array {
        // Retrieve a number of right answers and total answers.
        [$numrightanswers, $total] = $this->get_num_parts_right($response);
        // Retrieve a number of wrong accent numbers.
        $numpartialanswers = $this->get_num_parts_partial($response);
        // Calculate fraction.
        $fraction = ($numrightanswers + $numpartialanswers - $numpartialanswers * $this->accentpenalty)
            / $total;

        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    public function get_num_parts_right(array $response): array {
        $numright = 0;
        foreach ($this->answers as $key => $answer) {
            $answerdata = $response[$this->field($key)];
            // In this case, the answer is absolutely correct.
            if ($this->is_full_fraction($answer, $answerdata)) {
                $numright++;
            }
        }
        return [$numright, count($this->answers)];
    }

    /**
     * Get number of answers are wrong accents.
     *
     * @param array $response The answer list.
     * @return int The number of partial answers.
     */
    public function get_num_parts_partial(array $response): int {
        $numpartial = 0;
        foreach ($this->answers as $key => $answer) {
            $answerdata = $response[$this->field($key)];
            // In this case, the answer is partial correct.
            if ($this->is_partial_fraction($answer, $answerdata)) {
                $numpartial++;
            }
        }

        return $numpartial;
    }

    public function clear_wrong_from_response(array $response): array {
        foreach ($this->answers as $key => $answer) {
            if (isset($response[$this->field($key)]) && !$answer->is_correct($response[$this->field($key)])) {
                $response[$this->field($key)] = '';
            }
        }
        return $response;
    }

    /**
     * Verify if the answer can receive full marks.
     * The answer must satisfy at least one of two conditions:
     * Condition 1 - the answer is completely correct, including accent characters;
     * Condition 2 - the answer has the same letter characters but incorrect accent characters
     * and the accent grading type of the question is disregarded.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $inputanswer The inputanswer need to calculate.
     * @return bool The result of answer. True if it's correct.
     */
    public function is_full_fraction(qtype_crossword\answer $answer, string $inputanswer): bool {
        return $answer->is_correct($inputanswer) || ($this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_IGNORE &&
                $answer->is_wrong_accents($inputanswer));
    }

    /**
     * Verify if the answer can receive apart from marks.
     * The answer must satisfy two conditions:
     * Condition 1 - the answer is wrong accent only;
     * Condition 2 - the accent grading type of the question is penalty.
     *
     * @param qtype_crossword\answer $answer The answer object.
     * @param string $inputanswer The inputanswer need to calculate.
     * @return bool The result of answer. True if it's partial correct.
     */
    public function is_partial_fraction(qtype_crossword\answer $answer, string $inputanswer): bool {
        return $answer->is_wrong_accents($inputanswer) &&
            $this->accentgradingtype === \qtype_crossword::ACCENT_GRADING_PENALTY;
    }

    /**
     * Retrieve the valid answers list.
     *
     * @param array $answers The answers list.
     * @return array The filtered list.
     */
    private function filter_answers(array $answers): array {
        $response = array_filter($answers, function(string $answer) {
            return core_text::strlen(trim(str_replace('_', '', $answer))) > 0;
        });
        return $response;
    }

    public function check_file_access($qa, $options, $component, $filearea,
        $args, $forcedownload) {

        if ($component === 'question' && ($filearea === 'clue' || $filearea === 'feedback')) {
            foreach ($qa->get_question(false)->answers as $answer) {
                if ((int)$answer->answerid === (int)$args[0]) {
                    return true;
                }
            }
        }

        if ($component == 'question' && in_array($filearea,
                ['correctfeedback', 'partiallycorrectfeedback', 'incorrectfeedback'])) {
            return $this->check_combined_feedback_file_access($qa, $options, $filearea, $args);
        }

        return parent::check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload);
    }
}

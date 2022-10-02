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

/**
 * Class that represents a crossword question.
 */
class qtype_crossword_question extends question_graded_automatically {

    /** @var object The answers object. */
    public $answers;

    /** @var int The row number. */
    public $numrows;

    /** @var int The column number. */
    public $numcolumns;

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
        list($right, $total) = $this->get_num_parts_right($response);
        $fraction = $right / $total;
        return [$fraction, question_state::graded_state_for_fraction($fraction)];
    }

    public function get_num_parts_right(array $response): array {
        $answers = array_map(function($answer) {
            return $answer->answer;
        }, $this->answers);

        $numright = count($answers) - count(array_diff($answers, $response));
        return [$numright, count($answers)];
    }

    public function clear_wrong_from_response(array $response): array {
        $key = 0;
        foreach ($response as $answer) {
            if ($this->answers[$key]->answer !== $answer) {
                $response[$this->field($key)] = '';
            }
            $key++;
        }
        return $response;
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
}

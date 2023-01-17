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

namespace qtype_crossword;

/**
 * This defines a structured class to hold crossword question answers.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype_crossword
 * @copyright 2021, The Open University
 */
class answer {

    // Class properties.

    /** @var int The answers id value. */
    public $answerid;

    /** @var string The answers value. */
    public $answer;

    /** @var string The clues value. */
    public $clue;

    /** @var int The clues value. */
    public $clueformat;

    /** @var string The orientations value. */
    public $orientation;

    /** @var string The startrow value. */
    public $startrow;

    /** @var string The startcolumn value. */
    public $startcolumn;

    /** @var string The feedback value. */
    public $feedback;

    /** @var int The clues value. */
    public $feedbackformat;

    /**
     * Answer constructor.
     * @param int $answerid
     * @param string $answer
     * @param string $clue
     * @param int $clueformat
     * @param string $orientation
     * @param string $startrow
     * @param string $startcolumn
     * @param string $feedback
     * @param string $feedbackformat
     */
    public function __construct(int $answerid, string $answer, string $clue, int $clueformat, string $orientation,
        string $startrow, string $startcolumn, ?string $feedback, ?int $feedbackformat) {
        $this->answerid = $answerid;
        $this->answer = \qtype_crossword\util::safe_normalize($answer);
        $this->clue = $clue;
        $this->clueformat = $clueformat;
        $this->orientation = $orientation;
        $this->startrow = $startrow;
        $this->startcolumn = $startcolumn;
        $this->feedback = $feedback;
        $this->feedbackformat = $feedbackformat;
    }

    /**
     * Check the correctness of the answer,
     * Remove the underscore character with a space before comparing it.
     *
     * @param string $answer The answer need to be checked, maybe contain underscore characters.
     * @return bool The result after check, True if correct.
     */
    public function is_correct(string $answer): bool {
        return $this->answer === str_replace('_', ' ', $answer);
    }
}

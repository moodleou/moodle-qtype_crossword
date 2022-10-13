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
    /** @var string The answers value. */
    public $answer;

    /** @var string The clues value. */
    public $clue;

    /** @var string The orientations value. */
    public $orientation;

    /** @var string The startrow value. */
    public $startrow;

    /** @var string The startcolumn value. */
    public $startcolumn;

    /**
     * Answer constructor.
     * @param string $answer
     * @param string $clue
     * @param string $orientation
     * @param string $startrow
     * @param string $startcolumn
     */
    public function __construct(string $answer, string $clue, string $orientation,
        string $startrow, string $startcolumn) {
        $this->answer = \qtype_crossword\util::safe_normalize($answer);
        $this->clue = $clue;
        $this->orientation = $orientation;
        $this->startrow = $startrow;
        $this->startcolumn = $startcolumn;
    }
}

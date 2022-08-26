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
    /** @var array The answers list. */
    public $answer;

    /** @var array The clues list. */
    public $clue;

    /** @var array The orientations list. */
    public $orientation;

    /** @var array The startrow list. */
    public $startrow;

    /** @var array The startcolumn list. */
    public $startcolumn;

    /** @var int The numrows list. */
    public $numrows;

    /** @var int The numcolumns list. */
    public $numcolumns;

    /**
     * Answer constructor.
     * @param array $answer
     * @param array $clue
     * @param array $orientation
     * @param array $startrow
     * @param array $startcolumn
     * @param int $numrows
     * @param int $numcolumns
     */
    public function __construct(array $answer = [], array $clue = [], array $orientation = [],
            array $startrow = [], array $startcolumn = [], int $numrows = 7, int $numcolumns = 7) {
        $this->answer = $answer;
        $this->clue = $clue;
        $this->orientation = $orientation;
        $this->startrow = $startrow;
        $this->startcolumn = $startcolumn;
        $this->numrows = $numrows;
        $this->numcolumns = $numcolumns;
    }

    /**
     * Create list answers from input data.
     *
     * @param array $answers Answer list data input.
     * @return object Answer list object.
     */
    public function create_from_data(array $answers): object {
        foreach ($answers as $answer) {
            $this->answer[] = $answer->answer;
            $this->clue[] = $answer->clue;
            $this->orientation[] = $answer->orientation;
            $this->startrow[] = $answer->startrow;
            $this->startcolumn[] = $answer->startcolumn;
        }
        return $this;
    }
}

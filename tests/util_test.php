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
 * This file contains tests that walks a question through simulated student attempts.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license  https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_crossword;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once($CFG->dirroot . '/question/engine/tests/helpers.php');


/**
 * Unit tests for the crossword util.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license  https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class util_test extends \qbehaviour_walkthrough_test_base {

    /**
     * Test safe_normalize function.
     *
     * @dataProvider test_safe_normalize_provider
     * @covers \qtype_crossword\util::safe_normalize
     *
     * @param string $string1 The first string need to compare.
     * @param string $string2 The second string need to compare.
     */
    public function test_safe_normalize(string $string1, string $string2): void {
        $normalstring1 = \qtype_crossword\util::safe_normalize($string1);
        $normalstring2 = \qtype_crossword\util::safe_normalize($string2);
        $this->assertEquals($normalstring1, $normalstring2);
    }

    /**
     * Data provider for test_safe_normalize() test cases.
     *
     * @coversNothing
     * @return array List of data sets (test cases)
     */
    public function test_safe_normalize_provider(): array {
        return [
            'Normal case' => [
                'Hanoi',
                'Hanoi'
            ],
            'Same character but different representation code' => [
                'Amélie',
                'Amélie'
            ]
        ];
    }
}

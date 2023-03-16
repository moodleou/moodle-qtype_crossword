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

    /**
     * Test remove_accent function.
     *
     * @dataProvider test_remove_accent_provider
     * @covers \qtype_crossword\util::remove_accent
     *
     * @param string $containaccent The string contain accent characters.
     * @param string $missingaccent The string does not contain any accent characters.
     */
    public function test_remove_accent(string $containaccent, string $missingaccent): void {
        $accentremovedstring = \qtype_crossword\util::remove_accent($containaccent);
        $this->assertEquals($missingaccent, $accentremovedstring);
    }

    /**
     * Data provider for test_remove_accent() test cases.
     *
     * @coversNothing
     * @return array List of data sets (test cases)
     */
    public function test_remove_accent_provider(): array {
        return [
            'Normal case' => [
                'Hanoi',
                'Hanoi'
            ],
            'One wrong accent' => [
                'médecin',
                'medecin'
            ],
            'Two wrong accent' => [
                'pâté',
                'pate'
            ],
            'Three wrong accent' => [
                'téléphoné',
                'telephone'
            ],
        ];
    }

    /**
     * Test calculate_fraction_for_answer function.
     *
     * @dataProvider test_calculate_fraction_for_answer_provider
     * @covers \qtype_crossword\util::calculate_fraction_for_answer
     *
     * @param array $inputoptions List input options. It contains accent options
     * (ACCENT_GRADING_STRICT, ACCENT_GRADING_PENALTY, ACCENT_GRADING_IGNORE),
     * penalty for wrong accents and list input answers.
     * @param array $expectedfractions List expected fraction based on answer input.
     */
    public function test_calculate_fraction_for_answer(array $inputoptions, array $expectedfractions): void {
        // Create a crossword question which not accepted wrong accents.
        $q = \test_question_maker::make_question('crossword', 'not_accept_wrong_accents');
        // Set answer accents options.
        $q->accentgradingtype = $inputoptions['accentoption'];
        $q->accentpenalty = $inputoptions['accentpenalty'];
        foreach ($inputoptions['inputanswer'] as $key => $answerinput) {
            $fraction = \qtype_crossword\util::calculate_fraction_for_answer($q, $q->answers[$key], $answerinput);
            $this->assertEquals($expectedfractions[$key], $fraction);
        }
    }

    /**
     * Data provider for test_calculate_fraction_for_answer_for_answer() test cases.
     *
     * @coversNothing
     * @return array List of data sets (test cases).
     */
    public function test_calculate_fraction_for_answer_provider(): array {
        return [
            'Wrong accents are not accepted and the answers are absolutely correct.' => [
                'inputoptions' => [
                    'inputanswer' => ['PÂTÉ', 'TÉLÉPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_STRICT,
                    'accentpenalty' => 0,
                ],
                'fraction' => [1, 1],
            ],
            'Wrong accents are not accepted and 1 correct answer and 1 wrong accents answer.' => [
                'inputoptions' => [
                    'inputanswer' => ['PATE', 'TÉLÉPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_STRICT,
                    'accentpenalty' => 0,
                ],
                'fraction' => [0, 1],
            ],
            'Wrong accents are not accepted and both answer are wrong accents.' => [
                'inputoptions' => [
                    'inputanswer' => ['PATE', 'TELEPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_STRICT,
                    'accentpenalty' => 0,
                ],
                'fraction' => [0, 0],
            ],
            'Wrong accents are not accepted and both answers are wrong.' => [
                'inputoptions' => [
                    'inputanswer' => ['PETE', 'TALAPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_STRICT,
                    'accentpenalty' => 0,
                ],
                'fraction' => [0, 0],
            ],
            'Accept wrong accents but points will be deducted and answers are absolutely correct.' => [
                'inputoptions' => [
                    'inputanswer' => ['PÂTÉ', 'TÉLÉPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_PENALTY,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [1, 1],
            ],
            'Accept wrong accents but points will be deducted and one answer is wrong accents.' => [
                'inputoptions' => [
                    'inputanswer' => ['PATE', 'TÉLÉPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_PENALTY,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [0.5, 1],
            ],
            'Accept wrong accents but points will be deducted and both answer are wrong accents.' => [
                'inputoptions' => [
                    'inputanswer' => ['PATE', 'TELEPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_PENALTY,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [0.5, 0.5],
            ],
            'Accept wrong accents but points will be deducted and both answer are wrong' => [
                'inputoptions' => [
                    'inputanswer' => ['PETE', 'TALAPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_PENALTY,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [0, 0],
            ],
            'Accept wrong accents and answers are absolutely correct.' => [
                'inputoptions' => [
                    'inputanswer' => ['PÂTÉ', 'TÉLÉPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_IGNORE,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [1, 1],
            ],
            'Accept wrong accents and one answer is wrong accents.' => [
                'inputoptions' => [
                    'inputanswer' => ['PATE', 'TÉLÉPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_IGNORE,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [1, 1],
            ],
            'Accept wrong accents and both answer are wrong accents.' => [
                'inputoptions' => [
                    'inputanswer' => ['PATE', 'TELEPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_IGNORE,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [1, 1],
            ],
            'Accept wrong accents and both answer are wrong' => [
                'inputoptions' => [
                    'inputanswer' => ['PETE', 'TALAPHONE'],
                    'accentoption' => \qtype_crossword::ACCENT_GRADING_IGNORE,
                    'accentpenalty' => 0.5,
                ],
                'fraction' => [0, 0],
            ],
        ];
    }
}

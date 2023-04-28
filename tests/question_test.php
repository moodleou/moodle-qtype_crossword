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

namespace qtype_crossword;

use question_attempt_step;
use question_testcase;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/question/engine/tests/helpers.php');

/**
 * TUnit tests for qtype_crossword question.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class question_test extends \advanced_testcase {

    /**
     * Test is_complete_response function.
     *
     * @covers \qtype_crossword_question::is_complete_response
     */
    public function test_is_complete_response() {
        $question = \test_question_maker::make_question('crossword');

        $this->assertFalse($question->is_complete_response([]));
        $this->assertFalse($question->is_complete_response(['sub0' => '', 'sub1' => '', 'sub2' => 'ITALY']));
        $this->assertTrue($question->is_complete_response(['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY']));
    }

    /**
     * Test is_complete_response function.
     *
     * @dataProvider clear_wrong_from_response_provider
     * @covers \qtype_crossword_question::clear_wrong_from_response
     * @param array $responses submitted responses.
     * @param array $expected Expected result.
     */
    public function test_clear_wrong_from_response(array $responses, array $expected): void {
        $question = \test_question_maker::make_question('crossword');
        $this->assertEquals($expected, $question->clear_wrong_from_response($responses));
    }

    /**
     * Data provider for the test_clear_wrong_from_response.
     *
     * @coversNothing
     * @return array
     */
    public function clear_wrong_from_response_provider(): array {

        return [
            'Empty answer' => [
                [],
                []
            ],
            'Partial correct answers' => [
                ['sub0' => 'BRZIL', 'sub1' => 'PARI', 'sub2' => 'ITALY'],
                ['sub2' => 'ITALY', 'sub0' => '', 'sub1' => '']
            ],
            'Correct answers is not in ordered' => [
                ['sub1' => 'PARIS', 'sub0' => 'BRAZIL', 'sub2' => 'ITALY'],
                ['sub1' => 'PARIS', 'sub0' => 'BRAZIL', 'sub2' => 'ITALY']
            ],
            'Correct answers is in ordered' => [
                ['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY'],
                ['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY']
            ],
            'Not completed answers' => [
                ['sub1' => 'PARIS', 'sub2' => 'ITALY'],
                ['sub1' => 'PARIS', 'sub2' => 'ITALY']
            ],
            'Not completed and incorrect answer' => [
                ['sub1' => 'PARIS', 'sub2' => 'ITALI'],
                ['sub1' => 'PARIS', 'sub2' => '']
            ]
        ];
    }

    /**
     * Test function is_gradable_response.
     *
     * @covers \qtype_crossword_question::is_gradable_response
     */
    public function test_is_gradable_response() {
        $question = \test_question_maker::make_question('crossword');

        $this->assertFalse($question->is_gradable_response([]));
        $this->assertTrue($question->is_gradable_response(['sub0' => '', 'sub1' => '', 'sub2' => 'ITALY']));
        $this->assertTrue($question->is_gradable_response(['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY']));
    }

    /**
     * Test function grading.
     *
     * @param array $answeroptions List testcases with answer options.
     * @covers \qtype_crossword_question::grade_response
     * @dataProvider grading_provider
     */
    public function test_grading(array $answeroptions) {
        $question = \test_question_maker::make_question('crossword', 'not_accept_wrong_accents');
        $question->accentgradingtype = $answeroptions['options']['accentgradingtype'];
        $question->accentpenalty = $answeroptions['options']['accentpenalty'];
        foreach ($answeroptions['answers'] as $answer) {
            [$fraction, $state] = $question->grade_response($answer['answers']);
            $this->assertEqualsWithDelta($answer['fraction'], $fraction, question_testcase::GRADE_DELTA);
            $this->assertEqualsWithDelta($answer['state'], $state, question_testcase::GRADE_DELTA);
        }
    }

    /**
     * Test function get correct response.
     *
     * @covers \qtype_crossword_question::get_correct_response
     */
    public function test_get_correct_response() {
        $question = \test_question_maker::make_question('crossword');
        $this->assertEquals(['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY'], $question->get_correct_response());
    }

    /**
     * Test function filter_answer.
     *
     * @param array $response Data for a response.
     * @param int $expected Expected data.
     *
     * @covers \qtype_crossword_question::filter_answers
     * @dataProvider remove_blank_words_from_response_testcases
     */
    public function test_filter_answers(array $response, int $expectednumberofwords) {
        $this->resetAfterTest();
        $crossword = new \qtype_crossword_question();
        $method = new \ReflectionMethod(\qtype_crossword_question::class, 'remove_blank_words_from_response');
        $method->setAccessible(true);
        $this->assertCount($expectednumberofwords, $method->invoke($crossword, $response));
    }

    /**
     * Data provider for the test_filter_answers test.
     *
     * @return array
     */
    public function remove_blank_words_from_response_testcases(): array {

        return [
            'answer_valid_list' => [
                ['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY'],
                3
            ],
            'answer_invalid_list_with_underscore' => [
                ['sub0' => 'BRAZIL', 'sub1' => '____', 'sub2' => 'IT_LY'],
                2
            ],
            'answer_invalid_list_with_empty_string' => [
                ['sub0' => '', 'sub1' => '', 'sub2' => ''],
                0
            ]
        ];
    }

    /**
     * Test function get_num_parts_right.
     *
     * @param array $answeroptions List testcases with answer options.
     * @covers \qtype_crossword_question::get_num_parts_right
     * @dataProvider grading_provider
     */
    public function test_get_num_parts_right(array $answeroptions) {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('crossword', 'not_accept_wrong_accents');
        $question->start_attempt(new question_attempt_step(), 1);
        $question->accentgradingtype = $answeroptions['options']['accentgradingtype'];
        $question->accentpenalty = $answeroptions['options']['accentpenalty'];
        foreach ($answeroptions['answers'] as $answer) {
            [$numrightanswer, $totalanswer] = $question->get_num_parts_right($answer['answers']);
            $this->assertEquals($answer['numrightanswer'], $numrightanswer);
            $this->assertEquals(count($answer['answers']), $totalanswer);
        }
    }

    /**
     * Test function get_num_parts_partial.
     *
     * @param array $answeroptions List testcases with answer options.
     * @covers \qtype_crossword_question::get_num_parts_partial
     * @dataProvider grading_provider
     */
    public function test_get_num_parts_partial(array $answeroptions) {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('crossword', 'not_accept_wrong_accents');
        $question->start_attempt(new question_attempt_step(), 1);
        $question->accentgradingtype = $answeroptions['options']['accentgradingtype'];
        $question->accentpenalty = $answeroptions['options']['accentpenalty'];
        foreach ($answeroptions['answers'] as $answer) {
            $numanswerspartial = $question->get_num_parts_partial($answer['answers']);
            $this->assertEquals($answer['numpartialanswer'], $numanswerspartial);
        }
    }

    /**
     * Test function is_full_fraction.
     *
     * @param array $answeroptions List testcases with answer options.
     * @covers \qtype_crossword_question::is_full_fraction
     * @dataProvider grading_provider
     */
    public function is_full_fraction(array $answeroptions) {
        $this->resetAfterTest();
        $question = \test_question_maker::make_question('crossword', 'not_accept_wrong_accents');
        $question->start_attempt(new question_attempt_step(), 1);
        $question->accentgradingtype = $answeroptions['options']['accentgradingtype'];
        $question->accentpenalty = $answeroptions['options']['accentpenalty'];
        foreach ($answeroptions['answers'] as $answer) {
            $numanswerspartial = $question->is_full_fraction($answer);
            $this->assertEquals($answer['numpartialanswer'], $numanswerspartial);
        }
    }

    /**
     * Data provider for the get_num_parts_right and grading test.
     *
     * @coversNothing
     * @return array
     */
    public function grading_provider(): array {

        return [
            'Answer options not accepts wrong accented' => [
                [
                    'answers' => [
                        'Answer is absolutely correct' => [
                            'answers' => ['sub0' => 'PÂTÉ', 'sub1' => 'TÉLÉPHONE'],
                            'numrightanswer' => 2,
                            'numpartialanswer' => 0,
                            'fraction' => 1,
                            'state' => \question_state::$gradedright,
                        ],
                        'Answers with incorrect accents' => [
                            'answers' => ['sub0' => 'PATE', 'sub1' => 'TELEPHONE'],
                            'numrightanswer' => 0,
                            'numpartialanswer' => 0,
                            'fraction' => 0,
                            'state' => \question_state::$gradedwrong,
                        ],
                        'Answers are wrong' => [
                            'answers' => ['sub0' => 'PETE', 'sub1' => 'TALAPHONE'],
                            'numrightanswer' => 0,
                            'numpartialanswer' => 0,
                            'fraction' => 0,
                            'state' => \question_state::$gradedwrong,
                        ],
                    ],
                    'options' => [
                        'accentgradingtype' => \qtype_crossword::ACCENT_GRADING_STRICT,
                        'accentpenalty' => 0,
                    ],
                ],
            ],
            'Answer options accepts wrong accented but subtracts 10%' => [
                [
                    'answers' => [
                        'Answer is absolutely correct' => [
                            'answers' => ['sub0' => 'PÂTÉ', 'sub1' => 'TÉLÉPHONE'],
                            'numrightanswer' => 2,
                            'numpartialanswer' => 0,
                            'fraction' => 1,
                            'state' => \question_state::$gradedright,
                        ],
                        'Answers with incorrect accents' => [
                            'answers' => ['sub0' => 'PATE', 'sub1' => 'TELEPHONE'],
                            'numrightanswer' => 0,
                            'numpartialanswer' => 2,
                            'fraction' => 0.9,
                            'state' => \question_state::$gradedpartial,
                        ],
                        'Answers are wrong' => [
                            'answers' => ['sub0' => 'PETE', 'sub1' => 'TALAPHONE'],
                            'numrightanswer' => 0,
                            'numpartialanswer' => 0,
                            'fraction' => 0,
                            'state' => \question_state::$gradedwrong,
                        ],
                    ],
                    'options' => [
                        'accentgradingtype' => \qtype_crossword::ACCENT_GRADING_PENALTY,
                        'accentpenalty' => 0.1,
                    ],
                ],
            ],
            'Answer options accepts wrong accented and do not subtracts points' => [
                [
                    'answers' => [
                        'Answer is absolutely correct' => [
                            'answers' => ['sub0' => 'PÂTÉ', 'sub1' => 'TÉLÉPHONE'],
                            'numrightanswer' => 2,
                            'numpartialanswer' => 0,
                            'fraction' => 1,
                            'state' => \question_state::$gradedright,
                        ],
                        'Answers with incorrect accents' => [
                            'answers' => ['sub0' => 'PATE', 'sub1' => 'TELEPHONE'],
                            'numrightanswer' => 2,
                            'numpartialanswer' => 0,
                            'fraction' => 1,
                            'state' => \question_state::$gradedright,
                        ],
                        'Answers are wrong' => [
                            'answers' => ['sub0' => 'PETE', 'sub1' => 'TALAPHONE'],
                            'numrightanswer' => 0,
                            'numpartialanswer' => 0,
                            'fraction' => 0,
                            'state' => \question_state::$gradedwrong,
                        ],
                    ],
                    'options' => [
                        'accentgradingtype' => \qtype_crossword::ACCENT_GRADING_IGNORE,
                        'accentpenalty' => 0,
                    ],
                ],
            ],
        ];
    }
}

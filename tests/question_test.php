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
     * @covers \qtype_crossword_question::grade_response
     */
    public function test_grading() {
        $question = \test_question_maker::make_question('crossword');

        $this->assertEquals([0.66666666666666663, \question_state::$gradedpartial],
            $question->grade_response(['sub0' => 'LONDON', 'sub1' => 'PARIS', 'sub2' => 'ITALY']));
        $this->assertEquals([1, \question_state::$gradedright],
            $question->grade_response(['sub0' => 'BRAZIL', 'sub1' => 'PARIS', 'sub2' => 'ITALY']));

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
     * @covers \qtype_crossword_question::filter_answers
     * @dataProvider filter_answers_provider
     */
    public function test_filter_answers(array $answer, int $expected) {
        $this->resetAfterTest();
        $crossword = new \qtype_crossword_question();
        $method = new \ReflectionMethod(\qtype_crossword_question::class, 'filter_answers');
        $method->setAccessible(true);
        $result = $method->invoke($crossword, $answer);
        $this->assertEquals($expected, count($result));
    }

    /**
     * Data provider for {@link test_filter_answers()}.
     *
     * @coversNothing
     * @return array
     */
    public function filter_answers_provider(): array {

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
}

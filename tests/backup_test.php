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

use qtype_crossword;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . "/phpunit/classes/restore_date_testcase.php");
require_once($CFG->dirroot . '/question/type/crossword/questiontype.php');

/**
 * Unit tests for backup/restore process in crossword qtype.
 *
 * @package qtype_crossword
 * @copyright 2023 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_test extends \restore_date_testcase {

    /** @var qtype_crossword instance of the question type class to test. */
    protected $qtype;

    public function setUp(): void {
        $this->qtype = new qtype_crossword();
    }

    protected function tearDown(): void {
        $this->qtype = null;
    }

    /**
     * Load required libraries
     */
    public static function setUpBeforeClass(): void {
        global $CFG;
        require_once("{$CFG->dirroot}/backup/util/includes/restore_includes.php");
    }

    /**
     * Restore the studentquiz backup file in the fixture folder base on filemame.
     *
     * @param string $filename backup file name in the fixture folder.
     * @param string $coursefullname course full name to be restored.
     * @param string $courseshortname course short name to be restored.
     * @return mixed bool|stdClass return the studentquiz object restored.
     */
    protected function restore_crossword_question_backup_file_to_course_shortname(string $filename, string $coursefullname,
        string $courseshortname) {
        global $DB, $USER;
        $testfixture = __DIR__ . '/fixtures/' . $filename;

        // Extract our test fixture, ready to be restored.
        $backuptempdir = 'qtype_crossword';
        $backuppath = make_backup_temp_directory($backuptempdir);
        get_file_packer('application/vnd.moodle.backup')->extract_to_pathname($testfixture, $backuppath);
        // Do the restore to new course with default settings.
        $categoryid = $DB->get_field('course_categories', 'MIN(id)', []);
        $courseid = \restore_dbops::create_new_course($coursefullname, $courseshortname, $categoryid);

        $controller = new \restore_controller($backuptempdir, $courseid, \backup::INTERACTIVE_NO, \backup::MODE_GENERAL, $USER->id,
            \backup::TARGET_NEW_COURSE);

        $controller->execute_precheck();
        $controller->execute_plan();
        $controller->destroy();

        return $DB->get_record('question', []);
    }

    /**
     * Data provider for test_cw_backup_data().
     *
     * @coversNothing
     * @return array
     */
    public function test_cw_backup_data_provider(): array {

        return [
            'before upgrade feedback column' => [
                'filename' => 'crossword_pre_feedback_upgrade.mbz',
                'coursefullname' => 'before upgrade feedback column',
                'courseshortname' => 'bufc',
                'questionname' => 'crossword-001',
                'words' => [
                    [
                        'clue' => 'where is the Christ the Redeemer statue located in?',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => null,
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'BRAZIL'
                    ],
                    [
                        'clue' => 'Eiffel Tower is located in?',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => null,
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'PARIS'
                    ],
                    [
                        'clue' => 'Where is the Leaning Tower of Pisa?',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => null,
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'ITALY'
                    ]
                ],
                'version' => 4,
            ],
            'after upgrade feedback column' => [
                'filename' => 'crossword_after_feedback_upgrade.mbz',
                'coursefullname' => 'after upgrade feedback column',
                'courseshortname' => 'aufc',
                'questionname' => 'crossword-001',
                'words' => [
                    [
                        'clue' => '<p>where is the Christ the Redeemer statue located in?</p>',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => '<p dir="ltr" style="text-align: left;">You are correct.</p>',
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'BRAZIL'
                    ],
                    [
                        'clue' => '<p>Eiffel Tower is located in?</p>',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => '<p dir="ltr" style="text-align: left;">You are correct.<br></p>',
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'PARIS'
                    ],
                    [
                        'clue' => '<p>Where is the Leaning Tower of Pisa?</p>',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => '<p dir="ltr" style="text-align: left;">You are correct.<br></p>',
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'ITALY'
                    ]
                ],
                'version' => 4,
            ],
            'before upgrade feedback column 3.11' => [
                'filename' => 'crossword_before_feedback_upgrade 3.11.mbz',
                'coursefullname' => 'before upgrade feedback column 3.11',
                'courseshortname' => 'bufc311',
                'questionname' => 'crossword-001',
                'words' => [
                    [
                        'clue' => 'where is the Christ the Redeemer statue located in?',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => null,
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'BRAZIL'
                    ],
                    [
                        'clue' => 'Eiffel Tower is located in?',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => null,
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'PARIS'
                    ],
                    [
                        'clue' => 'Where is the Leaning Tower of Pisa?',
                        'clueformat' => FORMAT_HTML,
                        'feedback' => null,
                        'feedbackformat' => FORMAT_HTML,
                        'answer' => 'ITALY'
                    ]
                ],
                'version' => 3,
            ],
        ];
    }

    /**
     * Test old sq backup data from earlier version.
     *
     * @covers \restore_qtype_crossword_plugin
     * @dataProvider test_cw_backup_data_provider
     * @param string $filename file name of the backup file.
     * @param string $coursefullname course full name.
     * @param string $courseshortname course short name
     * @param string $questionname question name to check after restore.
     * @param array $expectedwords word data to be checked after restore.
     * @param int $version skip the test if backup version higher than current major version.
     */
    public function test_cw_backup_data(string $filename, string $coursefullname, string $courseshortname,
        string $questionname, array $expectedwords, int $version): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        if (self::get_moodle_version_major() < $version) {
            $this->markTestSkipped();
        }
        // Check question with question name is not exist before restore.
        $this->assertFalse($DB->record_exists('question', ['name' => $questionname]));

        $cw = $this->restore_crossword_question_backup_file_to_course_shortname($filename, $coursefullname, $courseshortname);
        $q = \question_bank::load_question($cw->id);
        $this->qtype->get_question_options($q);
        // Verify question exist after restore and question word options is correct.
        $this->assertEquals($questionname, $q->name);
        $count = 0;
        foreach ($q->options->words as $word) {
            $this->assertEquals($expectedwords[$count]['clue'], $word->clue);
            $this->assertEquals($expectedwords[$count]['clueformat'], $word->clueformat);
            $this->assertEquals($expectedwords[$count]['feedback'], $word->feedback);
            $this->assertEquals($expectedwords[$count]['feedbackformat'], $word->feedbackformat);
            $this->assertEquals($expectedwords[$count]['answer'], $word->answer);
            $count++;
        }
    }

    /**
     * Get moodle version.
     *
     * @return int major moodle version number.
     */
    private static function get_moodle_version_major(): int {
        global $CFG;
        $versionarray = explode('.', $CFG->release);
        return (int) $versionarray[0];
    }
}

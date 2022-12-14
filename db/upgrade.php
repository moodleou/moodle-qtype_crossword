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
 * Essay question type upgrade code.
 *
 * @package    qtype
 * @subpackage crossword
 * @copyright  2022 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade code for the crossword question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_crossword_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2022101000) {

        // Changing precision of field clue on table qtype_crossword_words to (1333).
        $table = new xmldb_table('qtype_crossword_words');
        $field = new xmldb_field('clue', XMLDB_TYPE_CHAR, '1333', null, XMLDB_NOTNULL, null, null, 'questionid');

        // Launch change of precision for field clue.
        $dbman->change_field_precision($table, $field);

        // Crossword savepoint reached.
        upgrade_plugin_savepoint(true, 2022101000, 'qtype', 'crossword');
    }

    return true;
}

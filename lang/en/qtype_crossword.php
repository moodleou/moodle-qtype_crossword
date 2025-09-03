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
 * Plugin strings are defined here.
 *
 * @package qtype_crossword
 * @copyright 2022 The Open University
 * @license  https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['accentgradingignore'] = 'Just grade the letters and ignore any accents';
$string['accentgradingpenalty'] = 'Partial mark if the letters are correct but one or more accents are wrong';
$string['accentgradingstrict'] = 'Accented letters must completely match or the answer is wrong';
$string['accentletters'] = 'Accented letters';
$string['accentpenalty'] = 'Grade for answers with incorrect accents';
$string['across'] = 'Across';
$string['addmorewordblanks'] = 'Blanks for {no} more words';
$string['answer'] = 'Answer';
$string['answeroptions'] = 'Answer options';
$string['answerwithnumber'] = '{$a->number} {$a->orientation}: {$a->response}';
$string['celltitle'] = 'Row {row}, Column {column}. {number} {orientation}. {clue}, letter {letter} of {count}';
$string['clue'] = 'Clue';
$string['correctanswer'] = 'Correct answer: {$a}';
$string['down'] = 'Down';
$string['inputlabel'] = '{$a->number} {$a->orientation}. {$a->clue} Answer length {$a->length}';
$string['missingresponse'] = '-';
$string['mustbealphanumeric'] = 'The answer must contain alphanumeric characters. Special characters allowed are hyphens and apostrophes.';
$string['notenoughwords'] = 'This type of question requires at least {$a} word';
$string['numberofcolumns'] = 'Number of columns';
$string['numberofrows'] = 'Number of rows';
$string['orientation'] = 'Orientation';
$string['overflowposition'] = 'The word start or end position is outside the defined grid size.';
$string['pleaseananswerallparts'] = 'Please answer all parts of the question.';
$string['pleaseenterclueandanswer'] = 'You must enter both answer and clue for word {$a}.';
$string['pluginname'] = 'Crossword';
$string['pluginname_help'] = 'Crossword questions require the respondent to correctly fill-in the crossword grid. The question author is able to set options related to dealing with accented letters.';
$string['pluginnameadding'] = 'Adding a Crossword question';
$string['pluginnameediting'] = 'Editing a Crossword question';
$string['pluginnamesummary'] = 'A simple text-based crossword question. It currently requires the author to manually place the words (and therefore the overlapping letters) in the grid. It supports HTML content clues, and answer-specific feedback. There is also a feature to award partial marks for words spelt without correctly accented characters.';
$string['preview'] = 'Preview';
$string['privacy:metadata'] = 'The Crossword plugin does not store any personal data.';
$string['refresh'] = 'Refresh preview';
$string['smart_straight_quote_matching'] = 'Quote/apostrophe matching';
$string['smart_straight_quote_matching_help'] = 'If the "Relaxed" option is enabled, then any curly (also known as "smart") quotes and apostrophes in the question authoring fields will be converted to the straight equivalent on save.';
$string['smart_straight_quote_matching_relaxed'] = 'Relaxed: all forms of quotes and apostrophes are interchangeable (default).';
$string['smart_straight_quote_matching_strict'] = 'Strict: all forms of quotes and apostrophes are unique.';
$string['startcolumn'] = 'Column index';
$string['startrow'] = 'Row index';
$string['updateform'] = 'Update the form';
$string['wordhdrhelper_help'] = '<p>As the crossword is generated from the word list, you can either generate a single crossword layout for all users, or use the \'Shuffle crossword layout on new attempt\' option to generate a new layout for each new attempt per student (word combinations allowing).</p>
<p>Add your words and clues using the text fields. If you want a specific word fixed on the grid, tick \'Fix word on grid\' and specify its orientation and placement.</p>
<p>Most characters are supported in this question type, from A-Z, 0-9, diacritics and currency symbols etc. Any curly quotation marks or apostrophes will be converted or interpreted as \'straight\' versions for ease of input and auto-marking.</p>
<p>Add more words by selecting the \'Blanks for 3 more words\' button. Any blank words will be removed when the question is saved.</p>';
$string['wordlabel'] = 'W{$a->number}{$a->orientation}';
$string['wordno'] = 'Word {$a}';
$string['words'] = 'Words';
$string['words_help'] = 'Please set at least one word and its matching clue, and define its direction and start position. Remember that the words are numbered in the grid according to their order in this section.';
$string['wrongadjacentcharacter'] = 'Two or more consecutive new word breaks detected. Please use a maximum of one between individual words. Note that this does not limit the number of new words in the answer itself.';
$string['wrongintersection'] = 'The letter at the intersection of two words do not match. The word cannot be placed here.';
$string['wrongoverlappingwords'] = 'There cannot be two words starting in the same place, in the same direction. This clue starts in the same place as "{$a}" above.';
$string['wrongplugin'] = 'This plugin is different from the existing qtype_crossword plugin. Please uninstall the existing plugin before installing this one.';
$string['wrongpositionhyphencharacter'] = 'Please do not add a hyphen before or after the last alphanumeric character.';
$string['wrongpositionspacecharacter'] = 'Please do not add a space before or after the last alphanumeric character.';
$string['yougot1right'] = '1 of your answers is correct.';
$string['yougotnright'] = '{$a->num} of your answers are correct.';

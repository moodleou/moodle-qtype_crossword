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
 * Atto custom steps definitions.
 *
 * @package    qtype_crossword
 * @copyright 2022 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Steps definitions to deal with the atto text editor
 *
 * @package    qtype_crossword
 * @copyright 2022 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_qtype_crossword extends behat_base {

    /**
     * Select characters from input.
     *
     * @When I select :length characters from position :position in the :input
     */
    public function i_select_characters_from_position_in_the(int $length, int $position, string $input): void {
        if (!$this->running_javascript()) {
            throw new ErrorException('Select character requires JavaScript');
        }

        // Select characters.
        $script = <<<EOF
                const id = [...document.querySelectorAll('.contain-clue .wrap-clue label.accesshide')]
                .find(el => el.innerText === '$input')?.getAttribute('for');
                const input = document.getElementById(id);
                input.selectionStart = $position - 1;
                input.selectionEnd = $position + $length - 1;
                input.click();
                input.focus();
EOF;
        $this->getSession()->getDriver()->executeScript($script);
    }

    /**
     * Enter unicode characters from input.
     *
     * @When I enter unicode character :characters in the crossword clue :input
     */
    public function i_enter_unicode_characters_in_the_crossword_clue(string $characters, string $input): void {
        if (!$this->running_javascript()) {
            throw new ErrorException('Enter unicode character requires JavaScript');
        }

        // Enter unicode characters.
        $script = <<<EOF
                const id = [...document.querySelectorAll('.contain-clue .wrap-clue label.accesshide')]
                .find(el => el.innerText === '$input')?.getAttribute('for');
                const input = document.getElementById(id);
                input.click();
                input.setSelectionRange(0,0);
                const event = new CompositionEvent('compositionend', {data: '$characters'});
                input.dispatchEvent(event)
EOF;
        $this->getSession()->getDriver()->executeScript($script);
    }
}

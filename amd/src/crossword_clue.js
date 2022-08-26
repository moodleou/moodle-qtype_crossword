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
 * Crossword clue class, handle any action relative to clue.
 *
 * @module qtype_crossword/crossword_clue
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {CrosswordQuestion} from 'qtype_crossword/crossword_question';

export class CrosswordClue extends CrosswordQuestion {

    /**
     * Constructor.
     *
     * @param {Object} options The settings for crossword.
     */
    constructor(options) {
        super(options);
    }

    /**
     * Set up for clue section.
     */
    setUpClue() {
        let {words, readonly} = this.options;
        const clueEls = this.options.crosswordEl
            .closest('.qtype_crossword-grid-wrapper')
            .querySelectorAll('.contain-clue .wrap-clue');
        clueEls.forEach(el => {
            const questionId = el.getAttribute('question-id');
            let word = words.find(o => o.number === parseInt(questionId));
            if (word) {
                const inputEl = el.querySelector('input');
                inputEl.value += this.makeUnderscore(word.length - inputEl.value.length);
                if (!readonly) {
                    inputEl.disabled = false;
                }
                // Add event for input.
                this.addEventForClueInput(inputEl, word);
            }
        });
    }

    /**
     * Add event to word input element.
     *
     * @param {Element} el The input element.
     * @param {String} word The word data.
     */
    addEventForClueInput(el, word) {
        const {readonly} = this.options;
        if (readonly) {
            return;
        }
        el.addEventListener('click', (e) => {
            let startIndex = e.target.selectionStart;
            if (startIndex >= word.length) {
                startIndex = word.length - 1;
            }
            this.focusCellByStartIndex(startIndex, word);
            this.focusClue();
            this.setStickyClue();
        });

        el.addEventListener('focus', (e) => {
            e.target.dispatchEvent(new Event('click'));
        });

        el.addEventListener('keypress', (e) => {
            e.preventDefault();
            const {words, wordNumber} = this.options;
            const word = words.find(o => o.number === parseInt(wordNumber));
            let {key, target} = e;
            let startIndex = target.selectionStart;
            key = this.replaceText(key);
            if (key === '') {
                return;
            }
            const gelEl = this.options.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
            if (gelEl) {
                gelEl.querySelector('text.crossword-cell-text').innerHTML = key.toUpperCase();
                this.bindDataToClueInput(gelEl, key.toUpperCase());
            }
            // Go to next letter.
            startIndex++;
            const nexEl = this.options.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
            if (nexEl) {
                this.toggleHighlight(word, nexEl);
                target.setSelectionRange(startIndex, startIndex);
            }
        });

        el.addEventListener('keyup', (event) => {
            event.preventDefault();
            const {words, wordNumber} = this.options;
            const {key, target} = event;
            let {value} = target;
            if ([this.ARROW_LEFT, this.ARROW_RIGHT].includes(key)) {
                const startIndex = target.selectionStart;
                const gEl = this.options.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
                if (gEl) {
                    this.toggleHighlight(word, gEl);
                }
            }
            if (key === this.DELETE || key === this.BACKSPACE) {
                const word = words.find(o => o.number === parseInt(wordNumber));
                let startIndex = target.selectionStart;
                if (!word) {
                    return;
                }
                value = value + this.makeUnderscore(word.length - value.length);
                target.value = value;
                this.syncLettersByText(value);
                this.syncFocusCellAndInput(target, startIndex);
            }

            if (key === this.END || key === this.HOME) {
                let startIndex = 0;
                const word = words.find(o => o.number === parseInt(wordNumber));
                if (!word) {
                    return;
                }
                if (key === this.END) {
                    startIndex = word.length - 1;
                }
                this.syncFocusCellAndInput(target, startIndex);
            }
        });

        el.addEventListener('paste', (event) => {
            event.preventDefault();
            let value = (event.clipboardData || window.clipboardData).getData('text');
            value = this.replaceText(value);
            this.syncLettersByText(value);
        });

        el.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key.toLowerCase() === this.Z_KEY) {
                e.preventDefault();
            }
            if (e.key === this.ENTER) {
                e.preventDefault();
            }
        });

        el.addEventListener('cut', (event) => {
            const selectString = document.getSelection().toString();
            const startIndex = event.target.selectionStart;
            let {value} = event.target;
            value = value.substring(0, startIndex) +
                value.substring(startIndex + selectString.length) +
                this.makeUnderscore(selectString.length);
            event.target.value = value;
            event.clipboardData.setData('text/plain', selectString);
            event.preventDefault();
            event.target.setSelectionRange(startIndex, startIndex);
            this.syncLettersByText(value);
        });
    }

    /**
     * Focus cell base on the start index.
     *
     * @param {Element} startIndex The start index.
     * @param {String} word The word data.
     */
    focusCellByStartIndex(startIndex, word) {
        let position = this.calculatePosition(word, startIndex);
        const rect = this.options.crosswordEl.querySelector(`g rect[x='${position.x}'][y='${position.y}']`);
        if (rect) {
            this.options.wordNumber = word.number;
            this.toggleHighlight(word, rect.closest('g'));
            this.updateLetterIndexForCells(word);
        }
    }

    /**
     * Focus crossword cell from the start index.
     *
     * @param {Element} target The element.
     * @param {Number} startIndex The start index.
     */
    syncFocusCellAndInput(target, startIndex) {
        const {wordNumber} = this.options;
        const gEl = this.options.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
        target.setSelectionRange(startIndex, startIndex);
        if (gEl) {
            this.toggleFocus(gEl);
        }
    }

    /**
     * Toggle the focus cell.
     *
     * @param {Element} gEl The word letter.
     */
    toggleFocus(gEl) {
        const focused = this.options.crosswordEl.querySelector('g rect.crossword-cell-focussed');
        if (focused) {
            focused.classList.remove('crossword-cell-focussed');
            focused.classList.add('crossword-cell-highlighted');
        }
        gEl.querySelector('rect').classList.add('crossword-cell-focussed');
    }
}

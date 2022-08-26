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
 * CrosswordQuestion base class handle every common function.
 *
 * @module qtype_crossword/crossword_question
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export class CrosswordQuestion {

    // Arrow Left key.
    ARROW_LEFT = 'ArrowLeft';

    // Arrow Right key.
    ARROW_RIGHT = 'ArrowRight';

    // Arrow Up key.
    ARROW_UP = 'ArrowUp';

    // Arrow Down key.
    ARROW_DOWN = 'ArrowDown';

    // End key.
    END = 'End';

    // Home key.
    HOME = 'Home';

    // Delete key.
    DELETE = 'Delete';

    // Backspace key.
    BACKSPACE = 'Backspace';

    // Z key.
    Z_KEY = 'z';

    // A key.
    A_KEY = 'a';

    // Enter key.
    ENTER = 'Enter';

    /**
     * Constructor for crossword question.
     *
     * @param {Object} options The input options for the crossword.
     */
    constructor(options) {
        let defaultOption = {
            colsNum: 10,
            rowsNum: 10,
            words: [],
            target: '#crossword',
            isPreview: false,
            previewSetting: {backgroundColor: '#ffffff', borderColor: '#000000', textColor: '#ffffff', conflictColor: '#f4cece'},
            cellWidth: 31,
            cellHeight: 31,
            wordNumber: -1,
            coordinates: ''
        };
        // Merge options.
        defaultOption = {...defaultOption, ...options};
        // Set options.
        this.options = defaultOption;
        // Get target element.
        const targetEls = document.querySelectorAll(defaultOption.target);
        for (let i = 0; i < targetEls.length; i++) {
            if (!targetEls[i].querySelector('svg')) {
                this.crosswordEl = targetEls[i];
                this.options.crosswordEl = targetEls[i];
                break;
            }
        }
    }

    /**
     * Get alphabet character from position number.
     *
     * @param {Number} i Position character number.
     * @return {String} Alphabet character.
     */
    getColumnLabel(i) {
        return String.fromCharCode("A".charCodeAt(0) + i - 1);
    }

    /**
     * The answer must not contain any special character.
     *
     * @param {String} answer The answer string need to be check.
     * @return {Boolean} The value data.
     */
    isInvalidAnswer = function(answer) {
        return /[-@!$%^&*()_+|~=`\\#{}\[\]:";'<>?,.\/]/gi.test(answer);
    };

    /**
     * Generate underscore letter by length.
     *
     * @param {Number} length Expected length.
     *
     * @return {String} Underscore string.
     */
    makeUnderscore(length) {
        const arr = Array.from({length}, () => '_');
        return arr.join('');
    }

    /**
     * Update the letter index of the word based on the word selected.
     *
     * @param {Object} word The word object.
     */
    updateLetterIndexForCells(word) {
        const {wordNumber} = this.options;
        const letterList = this.options.crosswordEl.querySelectorAll(`g[word*='(${wordNumber})']`);
        // Convert letterList to array to use sort function.
        const letterListArray = Array.prototype.slice.call(letterList, 0);
        let letterIndex = 0;
        // Rearrange the letters in the correct order.
        letterListArray.sort((a, b) => {
            let aValue = parseInt(a.querySelector('rect').getAttributeNS(null, 'x'));
            let bValue = parseInt(b.querySelector('rect').getAttributeNS(null, 'x'));
            if (word.orientation) {
                aValue = parseInt(a.querySelector('rect').getAttributeNS(null, 'y'));
                bValue = parseInt(b.querySelector('rect').getAttributeNS(null, 'y'));
            }
            return aValue - bValue;
        }).forEach(el => {
            // Update letter index.
            el.setAttributeNS(null, 'letterIndex', letterIndex);
            letterIndex++;
        });
    }

    /**
     * Toggle focus the clue.
     */
    focusClue() {
        const {wordNumber} = this.options;
        const containCrosswordEl = this.options.crosswordEl.closest('.qtype_crossword-grid-wrapper');
        const clueEl = containCrosswordEl.querySelector(`.wrap-clue[question-id='${wordNumber}']`);
        const clueFocusEl = containCrosswordEl.querySelector(`.wrap-clue.focus`);
        // Remove the current focus cell.
        if (clueFocusEl) {
            clueFocusEl.classList.remove('focus');
        }
        // Add focus cell.
        if (clueEl) {
            clueEl.classList.add('focus');
        }
    }

    /**
     * Set sticky clue for the mobile version.
     */
    setStickyClue() {
        const stickyClue = this.options.crosswordEl.closest('.qtype_crossword-grid-wrapper').querySelector('.sticky-clue');
        const {wordNumber, words} = this.options;
        const word = words.find(o => o.number === parseInt(wordNumber));
        if (!stickyClue && word) {
            return;
        }
        let strongEl = stickyClue.querySelector('strong');
        let spanEl = stickyClue.querySelector('span');
        if (!strongEl) {
            strongEl = document.createElement('strong');
            strongEl.classList.add('mr-1');
            stickyClue.append(strongEl);
        }
        if (!spanEl) {
            spanEl = document.createElement('span');
            stickyClue.append(spanEl);
        }
        strongEl.innerText = `${word.number} ${this.options.orientation[word.orientation]}`;
        spanEl.innerText = `${word.clue} (${word.length})`;
    }

    /**
     * Focus crossword cell from the start index.
     *
     * @param {String} value The value string need to be replaced.
     * @return {String} The value data.
     */
    replaceText(value) {
        return value.replace(/[-@!$%^&*()_+|~=`\\#{}\[\]:";'<>?,.\/]/gi, '');
    }

    /**
     * Bind data to the clue.
     *
     * @param {Element} gEl The word letter.
     * @param {String} key The letter data.
     */
    bindDataToClueInput(gEl, key) {
        const {words, cellWidth, cellHeight} = this.options;
        const rectEl = gEl.querySelector('rect');
        const conflictPointX = rectEl.getAttributeNS(null, 'x');
        const conflictPointY = rectEl.getAttributeNS(null, 'y');
        let letterIndex, value;
        if (gEl) {
            let wordIds = gEl.getAttributeNS(null, 'word').match(/\d+/g);
            wordIds.forEach(wordId => {
                const word = words.find(o => o.number === parseInt(wordId));
                if (word) {
                    const startPoint = this.calculatePosition(word, 0);
                    if (word.orientation) {
                        letterIndex = (parseInt(conflictPointY) - startPoint.y) / (cellHeight + 1);
                    } else {
                        letterIndex = (parseInt(conflictPointX) - startPoint.x) / (cellWidth + 1);
                    }
                    const clueInputEl = this.options.crosswordEl
                        .closest('.qtype_crossword-grid-wrapper')
                        .querySelector(`.wrap-clue[question-id='${wordId}'] input`);
                    value = this.replaceAt(clueInputEl.value, letterIndex, key);
                    clueInputEl.value = value.toUpperCase();
                }
            });
        }
    }

    /**
     * Calculate the position of each letter of the word.
     *
     * @param {Object} word The current word object.
     * @param {Number} key The letter index of word.
     *
     * @return {Object} The coordinates of letter.
     */
    calculatePosition(word, key) {
        const {cellWidth, cellHeight} = this.options;
        let x = (cellWidth * word.startColumn) + (word.startColumn + 1);
        let y = (cellHeight * word.startRow) + (word.startRow + 1);
        if (word.orientation) {
            y += (key * cellHeight) + key;
        } else {
            x += (key * cellWidth) + key;
        }
        return {x, y};
    }

    /**
     * Replace letter at index.
     *
     * @param {String} text Text need to be replaced.
     * @param {Number} index Letter index.
     * @param {String} char The replace letter.
     *
     * @return {String} Underscore string.
     */
    replaceAt(text, index, char) {
        let a = text.split('');
        if (a[index] !== undefined) {
            a[index] = char;
        }
        return a.join('');
    }

    /**
     * Sync data to crossword cell from text.
     *
     * @param {Element} text The text data.
     * @param {Boolean} [bindClue=false] Check if bind data into clue.
     */
    syncLettersByText(text, bindClue = true) {
        const {wordNumber} = this.options;
        for (let i in text) {
            const gEl = this.options.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${i}']`);
            if (gEl) {
                const letter = text[i].toUpperCase();
                const textEl = gEl.querySelector('text.crossword-cell-text');
                if (text[i] !== '_') {
                    textEl.innerHTML = letter;
                } else {
                    textEl.innerHTML = '';
                }
                if (bindClue) {
                    this.bindDataToClueInput(gEl, letter);
                }
            }
        }
    }

    /**
     * Toggle the highlight cells.
     *
     * @param {Object} word The word object.
     * @param {Element} gEl The g element.
     */
    toggleHighlight(word, gEl) {
        const {wordNumber, orientation, title} = this.options;
        const focus = wordNumber;
        const focusedEl = this.options.crosswordEl.querySelector('.crossword-cell-focussed');
        if (focusedEl) {
            focusedEl.classList.remove('crossword-cell-focussed');
        }
        // Remove current highlight cells.
        this.options.crosswordEl.querySelectorAll('.crossword-cell-highlighted')
            .forEach(el => el.classList.remove('crossword-cell-highlighted'));
        // Set highlight cells.
        this.options.crosswordEl.querySelectorAll(`g[word*='(${focus})'] rect`)
            .forEach(el => {
                    let titleData = '';
                    if (el.closest('g').getAttributeNS(null, 'code') === gEl.getAttributeNS(null, 'code')) {
                        el.classList.add('crossword-cell-focussed');
                        // Update aria label.
                        let letterIndex = parseInt(el.closest('g').getAttributeNS(null, 'letterIndex'));
                        const data = {
                            row: word.startRow + 1,
                            column: word.startColumn + letterIndex + 1,
                            number: word.number,
                            orientation: orientation[word.orientation],
                            clue: word.clue,
                            letter: letterIndex + 1,
                            count: word.length
                        };
                        if (word.orientation) {
                            data.row = word.startRow + letterIndex + 1;
                            data.column = word.startColumn + 1;
                        }
                        titleData = this.replaceStringData(title, data);
                        this.options.crosswordEl.querySelector('input.crossword-hidden-input')
                            .setAttributeNS(null, 'aria-label', titleData);

                    } else {
                        el.classList.add('crossword-cell-highlighted');
                    }
                }
            );
    }

    /**
     * Replace string data.
     *
     * @param {String} str The string need to be replaced.
     * @param {Object} data The data.
     *
     * @return {String} The replaced string.
     */
    replaceStringData(str, data) {
        for (let key in data) {
            str = str.replace(`{${key}}`, data[key]);
        }
        return str;
    }

    /**
     * Sync data between clue section and crossword.
     */
    syncDataForInit() {
        const {words} = this.options;
        // Loop every input into clue section.
        this.options.crosswordEl.closest('.qtype_crossword-grid-wrapper').querySelectorAll('.wrap-clue input')
            .forEach(element => {
                // Tricky, update word number.
                this.options.wordNumber = parseInt(element.closest('.wrap-clue').getAttribute('question-id'));
                const word = words.find(o => o.number === this.options.wordNumber);
                if (!word) {
                    return;
                }
                // Sorting and Updating letter index.
                this.updateLetterIndexForCells(word);
                // The value will be filled into the valid cell.
                this.syncLettersByText(element.value, false);
            });
        // Set wordNumber by default value.
        this.options.wordNumber = -1;
    }
}

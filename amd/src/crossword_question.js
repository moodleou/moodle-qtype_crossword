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

    // Maximum row of crossword.
    MAX_ROW = 30;

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
            coordinates: '',
            maxSizeCell: 50,
            minSizeCell: 30,
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
                if (!this.options.isPreview) {
                    this.options.words = this.retrieveWordData();
                }
                break;
            }
        }
    }

    /**
     * Get word data.
     *
     * @return {Array} Word data list.
     */
    retrieveWordData() {
        const clueEls = this.options.crosswordEl
            .closest('.qtype_crossword-grid-wrapper')
            .querySelectorAll('.contain-clue .wrap-clue');
        if (clueEls.length === 0) {
            return [];
        }
        return [...clueEls].map(el => {
            const number = parseInt(el.dataset.questionid);
            const startRow = parseInt(el.dataset.startrow);
            const startColumn = parseInt(el.dataset.startcolumn);
            const length = parseInt(el.dataset.length);
            const orientation = parseInt(el.dataset.orientation);
            const clue = el.dataset.clue;
            return {number, startRow, startColumn, length, orientation, clue};
        }).sort((clueA, clueB) => clueA.number - clueB.number);
    }

    /**
     * Get alphabet character from the index.
     *
     * @param {Number} index The character index number start from 0.
     *
     * @return {String} Alphabet character, In case index number higher than 25,
     *  we will add one letter before the current one like Excel: AA, AB, AC, AD, AE etc.
     */
    getColumnLabel(index) {
        let text = '';

        // Get the integer of division and subtraction by 1,
        // The firstLetterIndex will start from -1
        // and increments every index adding more 26.
        const firstLetterIndex = Math.trunc(index / 26) - 1;

        // Get remainder from division result.
        // The lastLetterIndex value is the index of the second letter.
        let lastLetterIndex = index % 26;

        // In case firstLetterIndex < -1 we will not show the first letter.
        if (firstLetterIndex > -1) {
            text = this.retrieveCharacterByIndex(firstLetterIndex);
        }
        // Adding the last letter.
        text += this.retrieveCharacterByIndex(lastLetterIndex);

        return text;
    }

    /**
     * Get alphabet character by index.
     *
     * @param {Number} index Position character number.
     * @return {String} Alphabet character.
     */
    retrieveCharacterByIndex(index) {
        return String.fromCharCode("A".charCodeAt(0) + index);
    }

    /**
     * The answer must not contain any special character.
     *
     * @param {String} answer The answer string need to be check.
     * @return {Boolean} The value data.
     */
    isInvalidAnswer = function(answer) {
        return /[-@!$%^&*()_+|~=`\\#{}[\]:";'<>?,./]/gi.test(answer);
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
        const letterList = this.options.crosswordEl.querySelectorAll(`g[data-word*='(${wordNumber})']`);
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
            el.dataset.letterindex = letterIndex;
            letterIndex++;
        });
    }

    /**
     * Toggle focus the clue.
     */
    focusClue() {
        const {wordNumber} = this.options;
        const containCrosswordEl = this.options.crosswordEl.closest('.qtype_crossword-grid-wrapper');
        const clueEl = containCrosswordEl.querySelector(`.wrap-clue[data-questionid='${wordNumber}']`);
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
        return value.replace(/[-@!$%^&*()_+|~=`\\#{}[\]:";'<>?,./]/gi, '');
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
            let wordIds = gEl.dataset.word.match(/\d+/g);
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
                        .querySelector(`.wrap-clue[data-questionid='${wordId}'] input`);
                    // Replace spaces with an underscore character before binding to the answer input.
                    if (key === ' ') {
                        key = '_';
                    }
                    value = this.replaceAt(clueInputEl.value, letterIndex, key);
                    clueInputEl.value = value.toUpperCase() + this.makeUnderscore(word.length - value.length);
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
     * @param {String} text The text data.
     * @param {Boolean} skipEmptyData Allow skip rendering blank answers,
     *      if false, we will update the crossword grid even if the answer input is blank.
     * @return {Boolean} Is valid text string.
     */
    syncLettersByText(text, skipEmptyData = true) {
        const {wordNumber} = this.options;
        // Skip empty string.
        if (text.replace(/_/g, '').length === 0 && skipEmptyData) {
            return false;
        }
        for (let i in text) {
            const gEl = this.options.crosswordEl.querySelector(`g[data-word*='(${wordNumber})'][data-letterindex='${i}']`);
            if (gEl) {
                const letter = text[i].toUpperCase();
                const textEl = gEl.querySelector('text.crossword-cell-text');
                if (text[i] !== '_') {
                    textEl.innerHTML = letter;
                } else {
                    textEl.innerHTML = '';
                }
                this.bindDataToClueInput(gEl, letter);
            }
        }
        return true;
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
        this.options.crosswordEl.querySelectorAll(`g[data-word*='(${focus})'] rect`)
            .forEach(el => {
                    let titleData = '';
                    if (el.closest('g').dataset.code === gEl.dataset.code) {
                        el.classList.add('crossword-cell-focussed');
                        // Update aria label.
                        let letterIndex = parseInt(el.closest('g').dataset.letterindex);
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
                this.options.wordNumber = parseInt(element.closest('.wrap-clue').dataset.questionid);
                const word = words.find(o => o.number === this.options.wordNumber);
                if (!word) {
                    return;
                }
                // Sorting and Updating letter index.
                this.updateLetterIndexForCells(word);
                // The value will be filled into the valid cell.
                this.syncLettersByText(element.value);
            });
        // Set wordNumber by default value.
        this.options.wordNumber = -1;
    }

    /**
     * Set size for crossword.
     *
     * @param {Element} svg The svg element.
     * @return {Element} The svg element after set size.
     */
    setSizeForCrossword(svg) {
        const {colsNum, maxSizeCell, minSizeCell} = this.options;
        // Get max width and min width for crossword with current max cell size and min cell size.
        const maxWidth = colsNum * (maxSizeCell + 1) + 1;
        const minWidth = colsNum * (minSizeCell + 1) + 1;
        // To avoid the case that the crossword has too high a height when we have many rows (eg 30) and too few columns (eg 3).
        // We will limit the maximum height of the crossword.
        // This reduces the size of the crossword but still ensures that the size of each cell keep in the range min and max sizes.
        const maxHeight = this.MAX_ROW * (minSizeCell + 1) + 1;
        svg.style.cssText = `max-width: ${maxWidth}px; min-width: ${minWidth}px;
            max-height: ${maxHeight}px;`;
        return svg;
    }
}

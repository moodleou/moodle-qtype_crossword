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
 * JavaScript to make crossword question.
 *
 * @module qtype_crossword/crossword
 * @copyright 2022 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since 3.11
 */

define(function() {

    'use strict';

    // Arrow Left key.
    const ARROW_LEFT = 'ArrowLeft';
    // Arrow Right key.
    const ARROW_RIGHT = 'ArrowRight';
    // Arrow Up key.
    const ARROW_UP = 'ArrowUp';
    // Arrow Down key.
    const ARROW_DOWN = 'ArrowDown';
    // End key.
    const END = 'End';
    // Home key.
    const HOME = 'Home';
    // Delete key.
    const DELETE = 'Delete';
    // Backspace key.
    const BACKSPACE = 'Backspace';
    // Z key.
    const Z_KEY = 'z';
    // A key.
    const A_KEY = 'a';
    // Enter key.
    const ENTER = 'Enter';
    // Regular expression pattern for input filter.
    const FILTER_REGEX = /[-@!$%^&*()_+|~=`\\#{}\[\]:";'<>?,.\/]/gi;

    /**
     * Object to handle Crossword.
     *
     * @param {Object} options List options for crossword.
     * @constructor
     */
    function CrossWordQuestion(options) {
        // Default options.
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
        this.options = defaultOption;
        this.isPreview = defaultOption.isPreview;

        // Get target element.
        const targetEls = document.querySelectorAll(defaultOption.target);
        for (let i = 0; i < targetEls.length; i++) {
            if (!targetEls[i].querySelector('svg')) {
                this.crosswordEl = targetEls[i];
                break;
            }
        }
    }

    /**
     * Build the background table.
     */
    CrossWordQuestion.prototype.buildBackgroundTable = function() {
        const alphabet = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
            'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'
        ];
        let {colsNum, rowsNum, previewSetting} = this.options;
        let style = previewSetting;

        // Create table element.
        const tableEl = document.createElement('table');

        // Preview mode will add one more columns and row to add the coordinate helper.
        colsNum++;
        rowsNum++;

        tableEl.className = 'crossword-grid';
        // Set the background color.
        tableEl.style.backgroundColor = style.backgroundColor;

        for (let i = 0; i < rowsNum; i++) {
            const rowEl = document.createElement('tr');
            rowEl.className = 'grid-row';
            for (let j = 0; j < colsNum; j++) {
                // Create square.
                let squareEl = document.createElement('td');
                squareEl.className = 'grid-square';
                squareEl.style.borderColor = style.borderColor;
                squareEl.style.color = style.color;

                if (i === 0 && j === 0) {
                    squareEl.classList.add('cell-white');
                }

                // Adding alphanumeric.
                if (i === 0 && j !== 0) {
                    squareEl.innerText = alphabet[j - 1];
                    squareEl.classList.add('square-indicate-horizontal');
                }
                if (i !== 0 && j === 0) {
                    squareEl.innerText = i;
                    squareEl.classList.add('square-indicate-vertical');
                }
                rowEl.append(squareEl);
            }
            tableEl.append(rowEl);
        }
        this.tableEl = tableEl;
        this.crosswordEl.innerHTML = tableEl.outerHTML;
    };

    /**
     * Add each cell into table.
     */
    CrossWordQuestion.prototype.addCell = function() {
        let {words, previewSetting, rowsNum, colsNum} = this.options;
        // Don't draw empty words.
        if (words.length === 0) {
            return;
        }
        for (let i = 0; i < words.length; i++) {
            let row = words[i].rowindex + 1;
            let column = words[i].columnindex + 1;
            let answerLength = words[i].answer.length;
            let realLength = answerLength + words[i].columnindex;
            let allowLength = parseInt(colsNum);
            let invalidWord = words[i].clue.trim() === '';
            // Add more columns and row for preview.
            row++;
            column++;

            if (!invalidWord) {
                invalidWord = FILTER_REGEX.test(words[i].answer);
            }

            if (words[i].orientation) {
                realLength = answerLength + words[i].rowindex;
                allowLength = parseInt(rowsNum);
            }

            for (let j = 0; j < words[i].answer.length; j++) {
                const number = i + 1;
                const squareEl = document.querySelector('.grid-row:nth-child(' + row + ') .grid-square:nth-child(' + column + ')');
                if (!squareEl) {
                    continue;
                }

                // Paint white background.
                squareEl.classList.add('background-white');

                if (j === 0) {
                    const labelEl = squareEl.querySelector('.word-label');
                    if (!labelEl) {
                        let spanEl = document.createElement('span');
                        spanEl.className = 'word-label';
                        spanEl.innerText = words[i]?.no ?? number;
                        squareEl.append(spanEl);
                    } else {
                        let label = labelEl.innerText;
                        label += ', ' + words[i]?.no ?? number;
                        labelEl.innerText = label;
                    }
                }
                const letter = words[i].answer[j].toUpperCase().trim() ?? '';
                const contentEl = squareEl.querySelector('span.word-content');
                if (!contentEl) {
                    let spanEl = document.createElement('span');
                    spanEl.className = 'word-content';
                    spanEl.innerText = letter;
                    squareEl.append(spanEl);
                } else {
                    let text = '';
                    const innerText = contentEl.innerText;
                    if (innerText.search(letter) < 0) {
                        text = innerText + ' | ' + letter;
                        squareEl.style.backgroundColor = previewSetting.conflictColor;
                        contentEl.innerText = text;
                    }
                }

                if (invalidWord || realLength > allowLength) {
                    squareEl.style.backgroundColor = previewSetting.conflictColor;
                }

                if (words[i].orientation) {
                    row++;
                } else {
                    column++;
                }
            }
        }
    };

    /**
     * Build crossword to attempt.
     */
    CrossWordQuestion.prototype.buildCrossword = function() {
        const options = this.options;
        // Setup size of crossword.
        this.options = {...options, width: options.colsNum * 32 + 1, height: options.rowsNum * 32 + 1};
        // Set up for clue input: maxlength, aria-label.
        this.setUpClue();
        // Draw crossword by SVG to support high contrast mode.
        this.drawCrosswordSVG();
        // Sync data between clue section and crossword cell.
        this.syncDataForInit();
        // Add event when resized screen.
        this.addEventResizeScreen();
    };

    /**
     * Set up for clue section.
     */
    CrossWordQuestion.prototype.setUpClue = function() {
        let {words, readonly} = this.options;
        const clueEls = this.crosswordEl.closest('.contain-crossword').querySelectorAll('.contain-clue .wrap-clue');
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
    };

    /**
     * Draw crossword by SVG element.
     */
    CrossWordQuestion.prototype.drawCrosswordSVG = function() {
        const options = this.options;
        const crosswordEl = this.crosswordEl;

        if (!crosswordEl) {
            return;
        }

        // Create background.
        let svg = this.createElementNSFrom(
            'svg',
            {
                'class': 'crossword-grid',
                viewBox: `0 0 ${options.width} ${options.height}`
            }
        );

        // Create black background.
        const rectEl = this.createElementNSFrom(
            'rect',
            {
                'class': 'crossword-grid-background',
                x: 0,
                y: 0,
                width: options.width,
                height: options.height
            }
        );
        svg.append(rectEl);

        // Create svg body.
        svg = this.createCrosswordBody(svg);

        // Create an input, by default, it will be hidden.
        const inputContainEl = this.createElementFrom(
            'div',
            {
                'class': 'crossword-hidden-input-wrapper'
            }
        );
        const inputEl = this.createElementFrom(
            'input',
            {
                type: 'text',
                'class': 'crossword-hidden-input',
                maxlength: 1,
                autocomplete: 'off',
                spellcheck: false,
                autocorrect: 'off'
            }
        );
        // Add event for word input.
        this.addEventForWordInput(inputEl);
        inputContainEl.append(inputEl);
        crosswordEl.append(svg, inputContainEl);
    };

    /**
     * Sync data between clue section and crossword.
     */
    CrossWordQuestion.prototype.syncDataForInit = function() {
        const {words} = this.options;
        // Loop every input into clue section.
        this.crosswordEl.closest('.contain-crossword').querySelectorAll('.wrap-clue input')
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
    };


    /**
     * Calculate position and add cell into the crossword.
     *
     * @param {Element} svg  The svg element.
     * @return {Element} The svg element.
     */
    CrossWordQuestion.prototype.createCrosswordBody = function(svg) {
        const {words, cellWidth, cellHeight} = this.options;
        let count = 0;
        for (let i in words) {
            const word = words[i];
            for (let key = 0; key < word.length; key++) {
                // Prepare attributes for g.
                const customAttribute = {
                    rowIndex: word.rowIndex,
                    columnIndex: word.columnIndex,
                    letterIndex: key,
                    word: '(' + word.number + ')',
                    code: 'A' + count
                };
                // Calculate the letter position.
                const position = this.calculatePosition(word, parseInt(key));
                // Create rect element with these position.
                const rectEl = this.createElementNSFrom(
                    'rect',
                    {
                        ...position,
                        width: cellWidth,
                        height: cellHeight,
                        'class': 'crossword-cell'
                    }
                );
                // Create g element with the attributes.
                let g = this.createElementNSFrom('g', {...customAttribute});
                // Get exist ting rect element.
                const existingRectElement = svg.querySelector(`rect.crossword-cell[x='${position.x}'][y='${position.y}']`);
                // Create text element to hold the letter.
                const textEl = this.createElementNSFrom(
                    'text',
                    {
                        x: position.x + 11,
                        y: position.y + 21,
                        'class': 'crossword-cell-text'
                    }
                );
                // Check if cell is not drawn.
                if (!existingRectElement) {
                    // Create cell.
                    g.append(rectEl);
                    // If it's the first cell of word.
                    // Draw word number.
                    if (parseInt(key) === 0) {
                        g = this.appendCellNumber(g, position, word.number);
                    }
                    g.append(textEl);
                    // Add event for cell.
                    this.addEventForG(g);
                    count++;
                    svg.append(g);
                } else {
                    let existingNumberElement = existingRectElement.closest('g').querySelector('text.crossword-cell-number');
                    let currentWord = existingRectElement.closest('g').getAttribute('word');
                    let g;
                    existingRectElement.closest('g').setAttributeNS(null, 'word', currentWord + '(' + word.number + ')');
                    if (parseInt(key) === 0) {
                        if (existingNumberElement) {
                            // Append word number, if this cell is existed another one.
                            existingNumberElement.append(', ' + word.number);
                        } else {
                            // Create new word number.
                            g = existingRectElement.closest('g');
                            this.appendCellNumber(g, position, word.number);
                        }
                    }
                }
            }
        }
        return svg;
    };

    /**
     * Calculate the position of each letter of the word.
     *
     * @param {Object} word The current word object.
     * @param {Number} key The letter index of word.
     *
     * @return {Object} The coordinates of letter.
     */
    CrossWordQuestion.prototype.calculatePosition = function(word, key) {
        const {cellWidth, cellHeight} = this.options;
        let x = (cellWidth * word.columnIndex) + (word.columnIndex + 1);
        let y = (cellHeight * word.rowIndex) + (word.rowIndex + 1);
        if (word.orientation) {
            y += (key * cellHeight) + key;
        } else {
            x += (key * cellWidth) + key;
        }
        return {x, y};
    };

    /**
     * Create word number for the cell.
     *
     * @param {Element} g The g element.
     * @param {Object} position The coordinates of letter.
     * @param {Number} wordNumber The word number.
     *
     * @return {Element} The g element.
     */
    CrossWordQuestion.prototype.appendCellNumber = function(g, position, wordNumber) {
        // Update position.
        const x = position.x + 1;
        const y = position.y + 9;
        let textNumber = this.createElementNSFrom(
            'text',
            {
                x,
                y,
                'class': 'crossword-cell-number'
            }
        );
        textNumber.append(wordNumber);
        g.append(textNumber);
        return g;
    };

     /**
      * Handle action when click on cell.
      *
      * @param {Element} gEl The g element.
      */
    CrossWordQuestion.prototype.handleWordSelect = function(gEl) {
        const currentCell = gEl.getAttributeNS(null, 'code');
        let words = gEl.getAttributeNS(null, 'word');
        let focus = -1;
        let {coordinates, wordNumber} = this.options;

        // Detect word number.
        words = words.match(/(\d+)/g);

        // Detect word number based on event click.
        // The focus variable is the new word number.
        if (currentCell === coordinates) {
            const indexCell = words.indexOf(wordNumber);
            if (words[indexCell + 1] !== undefined) {
                focus = words[indexCell + 1];
            } else {
                focus = words[0];
            }
        } else {
            // Update new coordinates.
            this.options.coordinates = currentCell;
            if (wordNumber < 0) {
                this.options.wordNumber = words[0];
            }
            if (words.includes(wordNumber)) {
                focus = wordNumber;
            } else {
                focus = words[0];
            }
        }
        // Update word number.
        this.options.wordNumber = focus;
        const word = this.options.words.find(o => o.number === parseInt(focus));
        if (!word) {
            return;
        }
        // Sorting and Updating letter index.
        this.updateLetterIndexForCells(word);
        // Toggle highlight and focused.
        this.toggleHighlight(word, gEl);
        // Focus the clue.
        this.focusClue();
        // Update sticky clue for mobile version.
        this.setStickyClue();
    };

    /**
     * Creates an element with the specified namespace URI and qualified name.
     *
     * @param {String} type
     * @param {Object} attributes
     *
     * @return {Element} The return element.
     */
    CrossWordQuestion.prototype.createElementNSFrom = function(type, attributes = {}) {
        const element = document.createElementNS('http://www.w3.org/2000/svg', type);
        for (let key in attributes) {
            element.setAttributeNS(null, key, attributes[key]);
        }
        return element;
    };

    /**
     * Create element with attributes.
     *
     * @param {String} type
     * @param {Object} attributes The attribute list.
     * @return {Element} The return element.
     */
    CrossWordQuestion.prototype.createElementFrom = function(type, attributes = {}) {
        const element = document.createElement(type);
        for (let key in attributes) {
            element.setAttribute(key, attributes[key]);
        }
        return element;
    };

    /**
     * Toggle focus the clue.
     */
    CrossWordQuestion.prototype.focusClue = function() {
        const {wordNumber} = this.options;
        const containCrosswordEl = this.crosswordEl.closest('.contain-crossword');
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
    };

    /**
     * Set sticky clue for the mobile version.
     */
    CrossWordQuestion.prototype.setStickyClue = function() {
        const stickyClue = this.crosswordEl.closest('.contain-crossword').querySelector('.sticky-clue');
        const {wordNumber, words} = this.options;
        const word = words.find(o => o.number === parseInt(wordNumber));
        if (stickyClue && word) {
            stickyClue.querySelector('strong').innerText = `${word.number} ${word.orientation ? 'Down' : 'Across'}`;
            stickyClue.querySelector('span').innerText = word.clue;
        }
    };

    /**
     * Update the letter index of the word based on the word selected.
     *
     * @param {Object} word The word object.
     */
    CrossWordQuestion.prototype.updateLetterIndexForCells = function(word) {
        const {wordNumber} = this.options;
        const letterList = this.crosswordEl.querySelectorAll(`g[word*='(${wordNumber})']`);
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
    };

    /**
     * Toggle the highlight cells.
     *
     * @param {Object} word The word object.
     * @param {Element} gEl The g element.
     */
    CrossWordQuestion.prototype.toggleHighlight = function(word, gEl) {
        const {wordNumber, orientation, title} = this.options;
        const focus = wordNumber;
        const focusedEl = this.crosswordEl.querySelector('.crossword-cell-focussed');
        if (focusedEl) {
            focusedEl.classList.remove('crossword-cell-focussed');
        }
        // Remove current highlight cells.
        this.crosswordEl.querySelectorAll('.crossword-cell-highlighted')
            .forEach(el => el.classList.remove('crossword-cell-highlighted'));
        // Set highlight cells.
        this.crosswordEl.querySelectorAll(`g[word*='(${focus})'] rect`)
            .forEach(el => {
                let titleData = '';
                if (el.closest('g').getAttributeNS(null, 'code') === gEl.getAttributeNS(null, 'code')) {
                    el.classList.add('crossword-cell-focussed');
                    // Update aria label.
                    let letterIndex = parseInt(el.closest('g').getAttributeNS(null, 'letterIndex'));
                    const data = {
                        row: word.rowIndex + 1,
                        column: word.columnIndex + letterIndex + 1,
                        number: word.number,
                        orientation: orientation[word.orientation],
                        clue: word.clue,
                        letter: letterIndex + 1,
                        count: word.length
                    };
                    if (word.orientation) {
                        data.row = word.rowIndex + letterIndex + 1;
                        data.column = word.columnIndex + 1;
                    }
                    titleData = this.replaceStringData(title, data);
                    this.crosswordEl.querySelector('input.crossword-hidden-input')
                        .setAttributeNS(null, 'aria-label', titleData);

                } else {
                    el.classList.add('crossword-cell-highlighted');
                }
            }
        );
    };

    /**
     * Bind data to the clue.
     *
     * @param {Element} gEl The word letter.
     * @param {String} key The letter data.
     */
    CrossWordQuestion.prototype.bindDataToClueInput = function(gEl, key) {
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
                    const clueInputEl = this.crosswordEl
                        .closest('.contain-crossword')
                        .querySelector(`.wrap-clue[question-id='${wordId}'] input`);
                    value = this.replaceAt(clueInputEl.value, letterIndex, key);
                    clueInputEl.value = value.toUpperCase();
                }
            });
        }
    };

    /**
     * Toggle the focus cell.
     *
     * @param {Element} gEl The word letter.
     */
    CrossWordQuestion.prototype.toggleFocus = function(gEl) {
        const focused = this.crosswordEl.querySelector('g rect.crossword-cell-focussed');
        if (focused) {
            focused.classList.remove('crossword-cell-focussed');
            focused.classList.add('crossword-cell-highlighted');
        }
        gEl.querySelector('rect').classList.add('crossword-cell-focussed');
    };

    /**
     * Replace string data.
     *
     * @param {String} str The string need to be replaced.
     * @param {Object} data The data.
     *
     * @return {String} The replaced string.
     */
    CrossWordQuestion.prototype.replaceStringData = function(str, data) {
        for (let key in data) {
            str = str.replace(`{${key}}`, data[key]);
        }
        return str;
    };

    /**
     * Focus cell base on the start index.
     *
     * @param {Element} startIndex The start index.
     * @param {String} word The word data.
     */
    CrossWordQuestion.prototype.focusCellByStartIndex = function(startIndex, word) {
        let position = this.calculatePosition(word, startIndex);
        const rect = this.crosswordEl.querySelector(`g rect[x='${position.x}'][y='${position.y}']`);
        if (rect) {
            this.options.wordNumber = word.number;
            this.toggleHighlight(word, rect.closest('g'));
            this.updateLetterIndexForCells(word);
        }
    };

    /**
     * Sync data to crossword cell from text.
     *
     * @param {Element} text The text data.
     * @param {Boolean} [bindClue=false] Check if bind data into clue.
     */
    CrossWordQuestion.prototype.syncLettersByText = function(text, bindClue = true) {
        const {wordNumber} = this.options;
        for (let i in text) {
            const gEl = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${i}']`);
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
    };

    /**
     * Focus crossword cell from the start index.
     *
     * @param {Element} target The element.
     * @param {Number} startIndex The start index.
     */
    CrossWordQuestion.prototype.syncFocusCellAndInput = function(target, startIndex) {
        const {wordNumber} = this.options;
        const gEl = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
        target.setSelectionRange(startIndex, startIndex);
        if (gEl) {
            this.toggleFocus(gEl);
        }
    };

    /**
     * Focus crossword cell from the start index.
     *
     * @param {String} value The value string need to be replaced.
     * @return {String} The value data.
     */
    CrossWordQuestion.prototype.replaceText = function(value) {
        return value.replace(FILTER_REGEX, '');
    };

    /**
     * Add event to the g element.
     *
     * @param {Element} g The g element.
     */
    CrossWordQuestion.prototype.addEventForG = function(g) {
        const {readonly} = this.options;
        if (readonly) {
            return;
        }
        // Handle event click.
        g.addEventListener('click', (e) => {
            const inputWrapperEl = this.crosswordEl.querySelector('.crossword-hidden-input-wrapper');
            const inputEl = inputWrapperEl.querySelector('input');
            let element = e.target;
            // Make sure select g.
            if (element.tagName !== 'g') {
                element = element.closest('g');
            }
            this.handleWordSelect(element);
            inputEl.setAttributeNS(null, 'code', element.getAttributeNS(null, 'code'));
            inputEl.value = '';
            this.updatePositionForCellInput(element.querySelector('rect'));
            inputEl.focus();
        });
    };

    /**
     * Set size and position for cell input.
     *
     * @param {Element} [rectEl=null] Rect element.
     */
    CrossWordQuestion.prototype.updatePositionForCellInput = function(rectEl = null) {
        if (rectEl === null) {
            rectEl = this.crosswordEl.querySelector('rect.crossword-cell-focussed');
        }
        if (rectEl) {
            const rect = rectEl.getBoundingClientRect();
            const parentEl = this.crosswordEl.querySelector('.crossword-grid').getBoundingClientRect();
            const inputWrapperEl = this.crosswordEl.querySelector('.crossword-hidden-input-wrapper');
            let top = rect.top - parentEl.top;
            if (top < 1) {
                top = 1;
            }
            inputWrapperEl.style.cssText = `
                display: block; top: ${top}px;
                left: ${rect.left - parentEl.left}px;
                width: ${rect.width}px;
                height: ${rect.height}px
            `;
        }
    };

    /**
     * Add event to word input element.
     *
     * @param {Element} inputEl The input element.
     */
    CrossWordQuestion.prototype.addEventForWordInput = function(inputEl) {
        const {readonly} = this.options;
        if (readonly) {
            return;
        }
        inputEl.addEventListener('keypress', (e) => {
            e.preventDefault();
            const {wordNumber} = this.options;
            const inputEl = e.target;
            const code = inputEl.getAttributeNS(null, 'code');
            let value = e.key.toUpperCase();
            if (this.replaceText(e.key) === '') {
                return false;
            }
            // Filter value.
            if (code) {
                const textEl = this.crosswordEl.querySelector(`g[code='${code}'] text.crossword-cell-text`);
                if (!textEl) {
                    return false;
                }
                textEl.innerHTML = value;
                const letterIndex = parseInt(textEl.closest('g').getAttributeNS(null, 'letterIndex'));
                const nextCellEl = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${letterIndex + 1}']`);
                // Interact with clue.
                this.bindDataToClueInput(textEl.closest('g'), e.key);
                if (nextCellEl) {
                    nextCellEl.dispatchEvent(new Event('click'));
                }
            }
        });

        inputEl.addEventListener('keyup', (event) => {
            event.preventDefault();
            const {wordNumber, cellWidth, cellHeight} = this.options;
            const {key, target} = event;
            const code = target.getAttributeNS(null, 'code');
            const gEl = this.crosswordEl.querySelector(`g[code='${code}']`);
            const letterIndex = parseInt(gEl.getAttributeNS(null, 'letterIndex'));
            const previousCell = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${letterIndex - 1}']`);
            const textEl = gEl.querySelector('text.crossword-cell-text');
            let x = parseInt(gEl.querySelector('rect').getAttributeNS(null, 'x'));
            let y = parseInt(gEl.querySelector('rect').getAttributeNS(null, 'y'));
            if (key === DELETE || key === BACKSPACE) {
                if (textEl.innerHTML === '') {
                    if (previousCell) {
                        previousCell.dispatchEvent(new Event('click'));
                    }
                } else {
                    textEl.innerHTML = '';
                    this.bindDataToClueInput(gEl, '_');
                }
            }
            if ([ARROW_UP, ARROW_DOWN, ARROW_LEFT, ARROW_RIGHT].includes(key)) {
                if (key === ARROW_UP) {
                    y -= (cellHeight + 1);
                }
                if (key === ARROW_DOWN) {
                    y += (cellHeight + 1);
                }
                if (key === ARROW_LEFT) {
                    x -= (cellWidth + 1);
                }
                if (key === ARROW_RIGHT) {
                    x += (cellWidth + 1);
                }
                const nextCell = this.crosswordEl.querySelector(`g rect[x='${x}'][y='${y}']`);
                if (nextCell) {
                    nextCell.closest('g').dispatchEvent(new Event('click'));
                }
            }
        });

        inputEl.addEventListener('click', (e) => {
            const inputEl = e.target;
            const code = inputEl.getAttributeNS(null, 'code');
            const gEl = this.crosswordEl.querySelector(`g[code='${code}']`);
            this.handleWordSelect(gEl);
        });

        inputEl.addEventListener('keydown', (e) => {
            let {key} = e;
            key = key.toLowerCase();
            if (e.ctrlKey) {
                if (
                    key === Z_KEY ||
                    key === A_KEY
                ) {
                    e.preventDefault();
                }
            }

            if (e.key === ENTER) {
                e.preventDefault();
            }
        });

        inputEl.addEventListener('paste', (e) => {
            e.preventDefault();
        });
    };

    /**
     * Add event to word input element.
     *
     * @param {Element} el The input element.
     * @param {String} word The word data.
     */
    CrossWordQuestion.prototype.addEventForClueInput = function(el, word) {
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
            const gelEl = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
            if (gelEl) {
                gelEl.querySelector('text.crossword-cell-text').innerHTML = key.toUpperCase();
                this.bindDataToClueInput(gelEl, key.toUpperCase());
            }
            // Go to next letter.
            startIndex++;
            const nexEl = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
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
            if ([ARROW_LEFT, ARROW_RIGHT].includes(key)) {
                const startIndex = target.selectionStart;
                const gEl = this.crosswordEl.querySelector(`g[word*='(${wordNumber})'][letterIndex='${startIndex}']`);
                if (gEl) {
                    this.toggleHighlight(word, gEl);
                }
            }
            if (key === DELETE || key === BACKSPACE) {
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

            if (key === END || key === HOME) {
                let startIndex = 0;
                const word = words.find(o => o.number === parseInt(wordNumber));
                if (!word) {
                    return;
                }
                if (key === END) {
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
            if (e.ctrlKey && e.key.toLowerCase() === Z_KEY) {
                e.preventDefault();
            }
            if (e.key === ENTER) {
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
    };

    /**
     * Add event to resize the screen width.
     */
    CrossWordQuestion.prototype.addEventResizeScreen = function() {
        window.addEventListener('resize', () => {
            this.updatePositionForCellInput();
        });
    };

    /**
     * Generate underscore letter by length.
     *
     * @param {Number} length Expected length.
     *
     * @return {String} Underscore string.
     */
    CrossWordQuestion.prototype.makeUnderscore = function(length) {
        const arr = Array.from({length}, () => '_');
        return arr.join('');
    };

    /**
     * Replace letter at index.
     *
     * @param {String} text Text need to be replaced.
     * @param {Number} index Letter index.
     * @param {String} char The replace letter.
     *
     * @return {String} Underscore string.
     */
    CrossWordQuestion.prototype.replaceAt = function(text, index, char) {
        var a = text.split('');
        if (a[index] !== undefined) {
            a[index] = char;
        }
        return a.join('');
    };

    /**
     * Singleton that tracks all the CrosswordQuestions on this page.
     *
     * @type {Object}
     */
    const questionManager = {

        /**
         * Initialise questions.
         *
         * @param {Object} options Setting options.
         */
        init: function(options) {
            const crossword = new CrossWordQuestion(options);
            if (options.isPreview) {
                crossword.buildBackgroundTable();
                crossword.addCell();
            } else {
                crossword.buildCrossword();
            }
        },

        /**
         * Add event questions.
         *
         * @param {Object} options Id.
         */
        addEventHandlersReloadQuestion: function(options) {
            const element = document.querySelector(options.element);
            if (element) {
                element.removeAttribute('disabled');
                element.addEventListener('click', function(event) {
                    event.preventDefault();
                    const columnEl = document.querySelector('select[name="numcolumns"]');
                    const rowEl = document.querySelector('select[name="numrows"]');
                    const words = questionManager.getWordsFromTable(options.target);
                    const settings = {...options,
                        words,
                        colsNum: columnEl.options[columnEl.selectedIndex].text,
                        rowsNum: rowEl.options[rowEl.selectedIndex].text
                    };
                    questionManager.init(settings);
                });
            }
        },

        /**
         * Get words from the table.
         *
         * @return {Object} The words object.
         */
        getWordsFromTable: function() {
            const answersEl = document.querySelectorAll('fieldset#id_wordhdr .fcontainer .form-group.row');
            const alphaRegex = /^[a-z]+/;
            let words = [];
            let i = 0;
            let no = 0;
            let word = {};

            if (!answersEl) {
                return words;
            }

            answersEl.forEach(obj => {
                let inputEl = obj.querySelectorAll('input[type="text"]');
                let selectEl = obj.querySelectorAll('select');

                if (inputEl.length > 0) {
                    inputEl.forEach(inputEl => {
                        const name = inputEl.name.match(alphaRegex)?.pop();
                        word[name] = inputEl.value.trim();
                    });
                }

                if (selectEl.length > 0) {
                    selectEl.forEach(selectEl => {
                        const name = selectEl.name.match(alphaRegex)?.pop();
                        word[name] = selectEl.selectedIndex;
                    });
                }
                i++;
                if (i !== 0 && i % 2 === 0) {
                    no++;
                    word.no = no;
                    words.push(word);
                    word = {};
                }
            });
            return words;
        }
    };

    /**
     * @alias module:qtype_crossword/crossword
     */
    return {
        /**
         * Initialise crossword question.
         *
         * @param {Object} options The option for the crossword question.
         */
        init: questionManager.init,

        /**
         * Add event to handle preview action.
         *
         * @param {Object} options The option for the crossword question.
         */
        addEventHandlersReloadQuestion: questionManager.addEventHandlersReloadQuestion
    };
});

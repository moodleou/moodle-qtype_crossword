# Change log for the crossword question type

## Changes in 1.1

* This version is compatible with Moodle 5.0.
* Fixes backup/restore issue to allow restoring crosswords with clues and feedback.
* Updates tooltip text for the help icons.
* Makes ‘Question text’ an optional field, as in most cases you don’t need any question text for this question type.
* Includes a few changes to fix display and form validation issues.
* Fixes the issue of entering special characters in the grid using an IME keyboard.
* Fixed issues in the crossword grid when Chinese characters were input.
* Adds automatic numbering and sorting of clues for the crossword grid.
* Adds a new setting to the form to treat all curly/straight punctuation marks as interchangeable (default=on).
* Fixes automated tests.
* Defined excluded hash fields and implemented conversion of legacy backup data
  to align with new question data format (per MDL-83541).
* Replace deprecated margin/text classes for Bootstrap 5.
* Updates CI and fixes codechecker issues.


## Changes in 1.0

* Basic implementation of this question type.
* Create crosswords for use in a Moodle quiz (or other places where questions can be used).
* In this version the words must be laid out in the grid manually, but the form validation will help you get that right.
* Supports characters in any alphabet that is in Unicode.
* With option for partial grading of words with accents.
* Supports answer containing spaces and hyphens.
* Feedback welcome.

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

namespace qtype_crossword;
use Normalizer;
use \qtype_crossword\answer;
use \qtype_crossword_question;
/**
 * Static utilities.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package qtype_crossword
 * @copyright 2022, The Open University
 */
class util {

    /** @var int The answer must be completely correct and must not be accents wrong */
    const DONT_ACCEPT_WRONG_ACCENTED = 0;

    /** @var int Accents errors are allowed, but points will be deducted. */
    const ACCEPT_WRONG_ACCENTED_BUT_PENALTY = 1;

    /** @var int Accents errors are allowed and the points will not be deducted. */
    const ACCEPT_WRONG_ACCENTED = 2;

    /**
     * Normalise a UTf-8 string to FORM_C, avoiding the pitfalls in PHP's
     * normalizer_normalize function.
     * @param string $string The input string.
     * @param int $normalizeform The form normalize. Default is FORM_KC.
     * @return string The normalised string.
     */
    public static function safe_normalize(string $string, int $normalizeform = Normalizer::FORM_KC): string {
        if ($string === '') {
            return '';
        }

        if (!function_exists('normalizer_normalize')) {
            return $string;
        }

        $normalised = normalizer_normalize($string, $normalizeform);
        if (is_null($normalised)) {
            // An error occurred in normalizer_normalize, but we have no idea what.
            debugging('Failed to normalise string: ' . $string, DEBUG_DEVELOPER);
            return $string; // Return the original string, since it is the best we have.
        }

        return $normalised;
    }

    /**
     * Remove accent character in text. Eg: Français -> Francais.
     *
     * @param string $string The input string.
     * @return string The normal string without accents.
     */
    public static function remove_accent(string $string): string {
        return preg_replace('/\p{Mn}/u', '', self::safe_normalize($string, Normalizer::FORM_KD));
    }

    /**
     * Calculate fraction of answer.
     *
     * @param qtype_crossword_question $question The question object.
     * @param answer $answer The answer object.
     * @param string $inputanswer The inputanswer need to calculate.
     * @return float The fraction value of the answer.
     */
    public static function calculate_fraction_for_answer(qtype_crossword_question $question,
            answer $answer, string $inputanswer): float {
        // Check the correctness of input answers. If its correct fraction will be 1.
        $fraction = (int) $answer->is_correct($inputanswer);
        // If the fraction is different from 1, we will check if the answer is incorrect in punctuation
        // and accentedlettersoptions allows for incorrect accents.
        if (!$fraction && $question->accentedlettersoptions !== self::DONT_ACCEPT_WRONG_ACCENTED
                && $answer->is_wrong_accents($inputanswer)) {
            $penaltypoint = 0;
            if ($question->accentedlettersoptions === self::ACCEPT_WRONG_ACCENTED_BUT_PENALTY) {
                // Set penalty point based on penaltyforincorrectaccents options.
                $penaltypoint = $question->penaltyforincorrectaccents;
            }

            return 1 - $penaltypoint;
        }

        return $fraction;
    }
}

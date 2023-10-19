<?php

namespace Helper;

defined('ABSPATH') or die('Direct access not allowed!');

class CalculateHelper
{

    /**
     * Get the lowest levenshtein cost function
     *
     * Calculate the levenshteion cost using php levenshtein for each mapped keywords.
     * Then get the lowest cost.
     *
     * @param array $haystack : array of word to compare, haystack should be 2 dimensional array.
     * @param string $string : String to evaluate.
     * @param integer $threshold : the max entry of levenshtein cost. Must be greater than or equal to 0. Default is 1 point;
     * @param integer $insertion : this is the insertion cost for levenshtein. Default is 1 point.
     * @param integer $replacement : this is the replacement cost for levenshtein. Default is 1 point.
     * @param integer $deletion : this is the deletion cost for levenshtein. Default is 1 point.
     * @param string $return : determine what the function return value is. Option is one of :
     * - haystack : return the array from haystack with the lowest levenshtein cost.
     * - key    : return the key of haystack with the lowest levenshtein cost.
     * - array  : return the array of levenshtein cost lower than the threshold point entry sorted by the cost.
     * default is 'key'.
     * @return mixed
     */
    public static function calcLevenshteinCost(array $haystack, String $string, Int $threshold = 1, Int $insertion = 1, Int $replacement = 1, Int $deletion = 1, String $return = 'key'): int|array|false
    {
        // Check if haystack is empty
        if (empty($haystack)) {
            throw new \Exception('Please provide the evaluated string haystack!');
        }

        /** (Disabled) Sort $haystack by key and check if is an associative array
         * array_is_list only work on php >= 8.1
         *
         * example :
         * var_dump(array_is_list([])); // true
         * var_dump(array_is_list(['a', 'b', 'c'])); // true
         * var_dump(array_is_list(["0" => 'a', "1" => 'b', "2" => 'c'])); // true
         * $haystack = ["1" => 'a', "0" => 'b', "2" => 'c'];
         * ksort($haystack); // Sort by key
         * var_dump(array_is_list($haystack)); // false is not sorted asc by key, true if sorted asc by key
         * var_dump(array_is_list(["1" => 'a', "0" => 'b', "2" => 'c'])); // false
         * var_dump(array_is_list(["a" => 'a', "b" => 'b', "c" => 'c'])); // false
         */
        // ksort($haystack);
        // if (array_is_list($haystack)) {
        //     throw new \Exception('Haystack must be an associative array!');
        // }

        /** Loop through keyword option */
        $levenshteinValue = []; // Set variable for the levenshtein value.
        $threshold = $threshold >= 0 ? $threshold : 10; // Set max threshold limit for each keyword.

        foreach ($haystack as $key => $keywordsHaystack) {
            // Count levenshtein cost for each keyword
            foreach ($keywordsHaystack as $word) {
                $levenshteinCost = levenshtein($word, $string, 1, 1, 1);

                // Store if cost is not pass the threshold
                if ((int)$levenshteinCost < $threshold) {
                    // Check if cost is already stored
                    if (array_key_exists($key, $levenshteinValue) && $levenshteinValue[$key] !== "" && $levenshteinValue[$key] !== null) {
                        // Set only lowest cost for each term
                        if ($levenshteinCost < (int)$levenshteinValue[$key]) {
                            $levenshteinValue[$key] = $levenshteinCost;
                        }
                    } else {
                        // Set new cost
                        $levenshteinValue[$key] = $levenshteinCost;
                    }
                }
            }
        }

        // If levenshteionValue is an array
        if (is_array($levenshteinValue) && !empty($levenshteinValue)) {
            // Sort by value ascending, form lowest to highest levenshtein cost
            asort($levenshteinValue);

            // Get the first key or key of the lowest levenshtein cost
            // This key is the term_id
            if (strtolower($return) == 'haystack') {
                $key = array_key_first($levenshteinValue);
                return $haystack[$key];
            } else if (strtolower($return) == 'array') {
                return $levenshteinValue;
            } else {
                return array_key_first($levenshteinValue);
            }
        } else {
            return false;
        }
    }
}

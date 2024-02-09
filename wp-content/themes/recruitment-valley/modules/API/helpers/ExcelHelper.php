<?php

namespace Helper;

class ExcelHelper
{
    /**
     * Convert letter to number function
     *
     * 1 : A
     * 2 : B
     * ...
     * 26 : Z
     *
     * Note :
     * - ord is convert letter to ASCII
     *
     * @param Int $number
     * @return String
     */
    public static function letterToNumber(String $letter)
    {
        $number = 0;
        $length = strlen($letter);
        for ($i = 0; $i < $length; $i++) {
            $number = $number * 26 + (ord($letter[$i]) - ord('A')) + 1;
        }
        return $number;
    }

    /**
     * Convert number to letter function
     *
     * 1 : A
     * 2 : B
     * ...
     * 26 : Z
     *
     * Note :
     * - chr is convert ASCII to letter
     *
     * @param Int $number
     * @return String
     */
    public static function numberToLetter(Int $number): String
    {
        $columnIndex = '';
        while ($number > 0) {
            $remainder = ($number - 1) % 26;
            $columnIndex = chr(65 + $remainder) . $columnIndex;
            $number = ($number - $remainder - 1) / 26;
        }
        return $columnIndex;
    }


    public static function getNextColumnLetter($columnIndex, $steps)
    {
        $columnNumber = self::letterToNumber($columnIndex);
        $columnNumber += $steps;
        return self::numberToLetter($columnNumber);
    }
}

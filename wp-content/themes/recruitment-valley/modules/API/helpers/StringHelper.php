<?php

namespace Helper;

class StringHelper
{
    public static function shortenDescription(String $description, Int $offset = 0, Int $length = 100)
    {
        $string = substr($description, $offset, $length);
        $string = preg_replace('/<\/?[^>]+|>/', ' ', $string); // Replace tag with whitespace
        $string = preg_replace('/\s+/', ' ', $string); // Trim multiple whitespace

        return trim($string, " ");
    }
}

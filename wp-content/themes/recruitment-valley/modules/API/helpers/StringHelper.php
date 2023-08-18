<?php

namespace Helper;

class StringHelper
{
    public static function shortenString(String $str, Int $offset = 0, Int $length = 100, String $suffix = '')
    {
        $string = substr($str, $offset, $length);

        $string = preg_replace('/<\/?[^>]+|>/', ' ', $string); // Replace tag with whitespace
        $string = preg_replace('/(&[\w#]+;)+/', ' ', $string); // Trim html special char
        $string = preg_replace('/\s+/', ' ', $string); // Trim multiple whitespace

        return trim($string, " ") . $suffix;
    }
}

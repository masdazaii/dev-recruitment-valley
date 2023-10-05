<?php
defined('ABSPATH') or die('Can\'t access directly');

class Helper
{
    public static function isset($variable, $key)
    {
        if (isset($variable[$key])) {
            return $variable[$key];
        } else {
            return null;
        }
    }

    public static function yt_id($url)
    {
        $parts = parse_url($url);
        parse_str($parts['query'], $query);

        return isset($query['v']) ? $query['v'] : '';
    }
}

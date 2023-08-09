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
}

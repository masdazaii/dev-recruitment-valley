<?php

namespace V\Rules;

use Exception;
use V\Rule;

class WPMetaExistsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null || empty($value)) {
            return false;
        } else {
            // user/meta_key/single,user_id
            // post/meta_key/single,post_id

            $params     = explode('/', $parameters[0]);
            $table      = $params[0];
            $metaKey    = $params[1];
            $single     = isset($params[2]) ?? false;
            $selector   = $parameters[1] ?? null; // post_id or user_id

            $this->check($value, $table, $metaKey, $single, $selector);
        }
        return true;
    }

    public function check($value, $table, $key, $single, $selector)
    {
        switch ($table) {
            case 'user':
                if (function_exists('get_user_meta')) {
                    $databaseValue = get_user_meta($selector, $key, $single);
                    if (is_array($databaseValue)) {
                        return in_array($value, $databaseValue) ? false : true;
                    } else {
                        return $databaseValue == $value;
                    }
                } else {
                    throw new Exception('Rule only work on wordpress application.');
                }
                break;
            case 'post':
                if (function_exists('get_post_meta')) {
                    $databaseValue = get_post_meta($selector, $key, $single);
                    if (is_array($databaseValue)) {
                        return in_array($value, $databaseValue) ? false : true;
                    } else {
                        return $databaseValue == $value;
                    }
                } else {
                    throw new Exception('Rule only work on wordpress application.');
                }
                break;
            case 'term':
                if (function_exists('get_term_meta')) {
                    $databaseValue = get_term_meta($selector, $key, $single);
                    if (is_array($databaseValue)) {
                        return in_array($value, $databaseValue) ? false : true;
                    } else {
                        return $databaseValue == $value;
                    }
                } else {
                    throw new Exception('Rule only work on wordpress application.');
                }
                break;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} value not found.";
    }
}

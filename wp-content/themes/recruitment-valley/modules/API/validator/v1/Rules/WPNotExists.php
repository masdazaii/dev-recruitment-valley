<?php

namespace V\Rules;

use V\Rule;
use Exception;

class WPNotExistsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        $params     = explode('/', $parameters[0]);
        $table      = $params[0];
        $type       = $params[1] ?? NULL;
        $selector   = $parameters[1] ?? null; // post_id or user_id or term_id or taxonomy

        if (is_array($field)) {
            if (is_array($value)) {
                if (count($value) > 0) {
                    foreach ($value as $val) {
                        if (!$this->check($val, $table, $type, $selector)) {
                            return false;
                            break;
                        }
                    }

                    return true;
                }

                return false;
            } else {
                return $this->check($value, $table, $type, $selector);
            }
        } else {
            if (empty($value) || $value == "" || $value == null) {
                return true;
            } else {
                return $this->check($value, $table, $type, $selector);
            }
        }
    }

    public function check($value, $table, $type, $selector): bool
    {
        switch ($table) {
            case 'user':
                if (function_exists('get_user_by')) {
                    return !get_user_by($selector, $value);
                } else {
                    throw new Exception('Rule only work on wordpress application.');
                }
            case 'post':
                if (function_exists('get_posts')) {
                    $args = [
                        'fields'         => 'ids',
                        'posts_per_page' => 1,
                        'orderby'        => 'ID',
                        'post_type'      => $type,
                        'post_status'    => 'publish',
                        // 'post__in'       => [$value]
                    ];

                    switch ($selector) {
                        case 'post_id':
                            $args['post__in'] = [$value];
                            break;
                        case 'post_name':
                            $args['name'] = $value;
                            break;
                    }

                    $databaseValue = get_posts($args);

                    return count($databaseValue) < 1 ? true : false;
                } else {
                    throw new Exception('Rule only work on wordpress application.');
                }
            case 'term':
                if (function_exists('get_term_by')) {
                    $checkTerm = get_term_by($selector, $value, $type);
                    return $checkTerm ? false : true;
                } else {
                    throw new Exception('Rule only work on wordpress application.');
                }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value exists.";
        } else {
            return "The {$field} exists.";
        }
    }
}

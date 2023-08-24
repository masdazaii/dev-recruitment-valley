<?php

namespace V\Rules;

use V\Rule;

class NotExistsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null) {
            return true;
        } else {
            $selector = $parameters[1] ?? null;
            $params = explode('/', $parameters[0]);
            $table = $params[0];
            $type = $params[1];
            $column = $params[2];

            $check = $this->check($value, $table, $type, $column, $selector);
            return $check;

            // if (is_array($value)) {
            //     // Loop through value
            //     foreach ($value as $val) {
            //         return is_numeric($val);
            //     }
            // } else {
            //     return is_numeric($value);
            // }
        }
    }

    public function check($value, $table, $type, $column, $selector)
    {
        switch ($table) {
            case 'user':
                switch ($type) {
                    case 'meta':
                        $databaseValue = get_user_meta($selector, $column, true);
                        if (is_array($databaseValue)) {
                            return in_array($value, $databaseValue) ? false : true;
                        } else {
                            return $databaseValue == $value ?? false;
                        }
                    case 'acf':
                        return true;
                    default:
                        return true;
                        // $dbValue = get_user()
                }
                break;
            case 'post':
                $args = [
                    'fields'         => 'ids',
                    'posts_per_page' => 1,
                    'orderby'        => 'ID',
                    'post_type'      => $type,
                    'post_status'    => 'publish',
                    'post__in'       => [$value]
                ];

                $databaseValue = get_posts($args);
                return count($databaseValue) > 0 ? false : true;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} not found.";
    }
}

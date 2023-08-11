<?php

namespace V\Rules;

use V\Rule;

class ExistsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null) {
            return true;
        } else {
            $params = explode('/', $parameters[0]);
            $table = $params[0];
            $type = $params[1];
            $column = $params[2];

            $check = $this->check($value, $table, $type, $column);
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

    public function check($value, $table, $type, $column)
    {
        switch ($table) {
            case 'user':
                switch ($column) {
                    case 'meta':
                        break;
                    case 'acf':
                        break;
                    default:
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
                return count($databaseValue) < 1 ? false : true;
                break;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} not found.";
    }
}

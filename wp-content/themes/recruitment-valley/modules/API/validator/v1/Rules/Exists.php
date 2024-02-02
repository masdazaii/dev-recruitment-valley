<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class ExistsRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value == "" || $value == null || empty($value)) {
            return true;
        } else {
            $params = explode('/', $parameters[0]);
            $table = $params[0];
            $type = $params[1];
            $column = $params[2];

            if (is_array($value)) {
                $checkValue = [];
                foreach ($value as $key => $values) {
                    $checkValue[$key] = $this->check($values, $table, $type, $column);
                }

                return in_array(false, $checkValue) ? false : true;
            } else {
                $check = $this->check($value, $table, $type, $column);
            }
            return $check;
        }
    }

    public function check($value, $table, $type, $column): bool
    {
        switch ($table) {
            case 'user':
                switch ($type) {
                    case 'meta':
                        $databaseValue = get_users([
                            'meta_key'      => $column,
                            'meta_value'    => $value
                        ]);
                        return count($databaseValue) > 0;
                        break;
                    case 'acf':
                        return true;
                        break;
                    default:
                        $databaseValue = get_user_by($column, $value);
                        return $databaseValue ? true : false;
                }
                break;
            case 'post':
                $args = [
                    'fields'         => 'ids',
                    'posts_per_page' => 1,
                    'orderby'        => 'ID',
                    'post_type'      => $type,
                    'post_status'    => 'publish',
                    // 'post__in'       => [$value]
                ];

                switch ($column) {
                    case 'post_id':
                        $args['post__in'] = [$value];
                        break;
                    case 'post_name':
                        $args['name'] = $value;
                        break;
                }

                $databaseValue = get_posts($args);

                return count($databaseValue) < 1 ? false : true;
            case 'term':
                $checkTerm = get_term_by($column, $value, $type);
                return $checkTerm ? true : false;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value didn't exists.";
        } else {
            return "The {$field} didn't exists.";
        }
    }
}

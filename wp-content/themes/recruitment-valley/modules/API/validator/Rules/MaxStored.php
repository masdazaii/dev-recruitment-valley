<?php

namespace V\Rules;

use V\Rule;

class MaxStoredRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        $params = explode('/', $parameters[1]);

        if ($params[0] === 'file') {
            $theField = strpos($field, '.*') !== false ? explode('.*.', $field)[0] : $field;

            if (!array_key_exists($theField, $_FILES) || count($_FILES[$theField]['name']) <= 0) {
                return true;
            } else {
                $limit = $parameters[0];
                $selector = $parameters[2];
                $table = $params[1];
                $type = $params[2];
                $column = $params[3];

                $check = $this->check(count($_FILES[$theField]['name']), $table, $type, $column, $selector, $limit);

                return $check;
            }
        } else {
            if ($value == "" || $value == null || empty($value)) {
                return true;
            } else {
                $table = $params[0];
                $type = $params[1];
                $column = $params[2];
                $limit = $params[3];

                return true;
            }
        }
    }

    public function check($value, $table, $type, $column, $selector, $limit): bool
    {
        switch ($table) {
            case 'user':
                switch ($type) {
                    case 'meta':
                        $databaseValue = maybe_unserialize(get_user_meta($selector, $column, true));

                        if (is_array($databaseValue)) {
                            if (count($databaseValue) + intval($value) <= $limit) {
                                return true;
                            } else {
                                return false;
                            }
                        } else {
                            if (intval($value) <= $limit) {
                                return true;
                            }
                            return false;
                        }
                        break;
                    default:
                        return true;
                }
                break;
            default:
                return true;
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "You've reached the stored limit. The {$field} max stored value is : " . $parameters[0] . ".";
    }
}

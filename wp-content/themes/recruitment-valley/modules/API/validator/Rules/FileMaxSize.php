<?php

namespace V\Rules;

use V\Rule;

class FileMaxSizeRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        $theField = strpos($field, '.*') !== false ? explode('.*', $field)[0] : $field;

        if (!array_key_exists($theField, $_FILES) || (is_array($_FILES[$theField]['name']) && count($_FILES[$theField]['name']) <= 0)) {
            return true;
        } else {
            if (strpos($field, '.*') !== false) {
                $checkValue = [];
                foreach ($value as $key => $values) {
                    $checkValue[$key] = $_FILES[$theField][$key]['size'] <= $parameters[0];
                }

                return in_array(false, $checkValue) ? false : true;
            } else {
                return (int)$_FILES[$theField]['size'] <= (int)$parameters[0];
            }
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

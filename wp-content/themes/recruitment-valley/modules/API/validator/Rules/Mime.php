<?php

namespace V\Rules;

use V\Rule;

class MimeRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        $theField = strpos($field, '.*') !== false ? explode('.*', $field)[0] : $field;

        if (!array_key_exists($theField, $_FILES) || count($_FILES[$theField]['name']) <= 0) {
            return true;
        } else {
            if (strpos($field, '.*') !== false) {
                $checkValue = [];
                foreach ($value as $key => $values) {
                    $checkValue[$key] = in_array(strtolower($_FILES[$theField][$key]['type']), $parameters);
                }

                return in_array(false, $checkValue) ? false : true;
            } else {
                return in_array(strtolower($_FILES[$theField]['type']), $parameters);
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "{$field} mime-type is not allowed. Allowed mime : " . implode(',', $parameters) . ".";
    }
}

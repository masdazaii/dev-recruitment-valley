<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

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
                    /** Size come as byte, then need to change size to KB */
                    $checkValue[$key] = ($_FILES[$theField][$key]['size'] / 1000) <= $parameters[0];
                }

                return in_array(false, $checkValue) ? false : true;
            } else {
                /** Size come as byte, then need to change size to KB */
                return ((int)$_FILES[$theField]['size'] / 1000) <= (int)$parameters[0];
            }
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        if (strpos($field, '.*') !== false) {
            return "One of the '" . substr($field, 0, -2) . "' value didn't exists.";
        } else {
            /** Size come as byte, then need to change size to KB */
            return "File size exceeded, allowed size : " . ($parameters[0] / 1000) . "MB";
        }
    }
}

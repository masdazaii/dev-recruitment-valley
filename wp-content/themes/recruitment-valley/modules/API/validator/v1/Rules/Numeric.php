<?php

namespace ValidifyMI\Rules;

use ValidifyMI\Rule;

class NumericRule implements Rule
{
    public function validate($field, $value, $parameters): bool
    {
        if ($value === "" || $value === null) {
            return true;
        }

        if (is_array($value)) {
            if (count($value) >= 1) {
                foreach ($value as $val) {
                    if (!is_numeric($val)) {
                        return false;
                        break;
                    }
                }

                return true;
            }
            return is_numeric($value);
        } else {
            return is_numeric($value);
        }
    }

    public function getErrorMessage($field, $parameters): string
    {
        return "The {$field} value must be a number.";
    }
}
